<?php

error_reporting(E_ALL);

//require_once($web_path . 'php-ews/EWS_Exception.php');

function __autoload($class_name) {
	global $web_path;
	$include_file = $web_path . 'php-ews/' . str_replace('_', '/', $class_name) . '.php';
	return (file_exists($include_file) ? require_once $include_file : false);
}

class EWS_Mail {

	var $ews;
	
	function __construct($host, $user, $password) {
		$this->ews = new ExchangeWebServices($host, $user, $password);
	}
	
	function mail_list()
	{
		$request = new EWSType_FindItemType();

		$request->ItemShape = new EWSType_ItemResponseShapeType();
		$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::DEFAULT_PROPERTIES;

		//$request->Traversal = EWSType_FolderQueryTraversalType::DEEP;
		$request->Traversal = EWSType_ItemQueryTraversalType::SHALLOW;

		$request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
		$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
		$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::INBOX;

		// sort order
		$request->SortOrder = new EWSType_NonEmptyArrayOfFieldOrdersType();
		$request->SortOrder->FieldOrder = array();
		$order = new EWSType_FieldOrderType();
		// sorts mails so that oldest appear first
		// more field uri definitions can be found from types.xsd (look for UnindexedFieldURIType)
		$order->FieldURI->FieldURI = 'item:DateTimeReceived';
		$order->Order = 'Ascending';
		$request->SortOrder->FieldOrder[] = $order;

		$request->IndexedPageItemView = new EWSType_IndexedPageViewType();
		$request->IndexedPageItemView->MaxEntriesReturned = 2;
		$request->IndexedPageItemView->BasePoint = 'Beginning';
		$request->IndexedPageItemView->Offset = 0;

		$request->Restriction = new EWSType_RestrictionType();
		$request->Restriction->IsNotEqualTo = new EWSType_IsNotEqualToType();

		// Search on the contact's given name.
		$request->Restriction->IsNotEqualTo->FieldURI = new EWSType_PathToUnindexedFieldType();
		$request->Restriction->IsNotEqualTo->FieldURI->FieldURI = 'message:IsRead';

		$request->Restriction->IsNotEqualTo->FieldURIOrConstant = new EWSType_FieldURIOrConstantType();
		$request->Restriction->IsNotEqualTo->FieldURIOrConstant->Constant = new EWSType_ConstantValueType();
		$request->Restriction->IsNotEqualTo->FieldURIOrConstant->Constant->Value = 1;

		$response = $this->ews->FindItem($request);
//		var_dump($response); exit;
		$mails = array();
		
		if (isset($response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->Message)) {
			if (is_array($response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->Message))
				$mails = $response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->Message;
			else
				$mails[] = $response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->Message;
		}
		
		return $mails;
	}
	
	function delete_mails($marked_ids, $isDelete = false)
	{
		if (is_array($marked_ids) && count($marked_ids) > 0) {
			if ($isDelete) {

				$request = new EWSType_DeleteItemType();
				$request->DeleteType = EWSType_DisposalType::MOVE_TO_DELETED_ITEMS;
				//$request->ItemIds = new EWSType_NonEmptyArrayOfBaseItemIdsType();
				$request->ItemIds->ItemId = array();
				foreach ($marked_ids as $mkid=>$mk_change_key) {
					$item = new EWSType_ItemIdType();
					$item->Id = $mkid;
					$item->ChangeKey = $mk_change_key;
					$request->ItemIds->ItemId[] = $item;
				}

				$response = $this->ews->DeleteItem($request);

			} else {

				$request = new EWSType_UpdateItemType();
				$request->MessageDisposition = 'SaveOnly';
				$request->ConflictResolution = 'AlwaysOverwrite';
				$request->ItemChanges = array();

				foreach ($marked_ids as $mkid=>$mk_change_key) {
	
					$change = new EWSType_ItemChangeType();
					$change->ItemId = new EWSType_ItemIdType();
					$change->ItemId->Id = $mkid;
					$change->ItemId->ChangeKey = $mk_change_key;
					//$change->Updates = new EWSType_NonEmptyArrayOfItemChangeDescriptionsType();

					$field = new EWSType_SetItemFieldType();
					$field->FieldURI = new EWSType_PathToUnindexedFieldType();
					$field->FieldURI->FieldURI = "message:IsRead";
					$field->Message = new EWSType_MessageType();
					$field->Message->IsRead = true;

					$change->Updates->SetItemField[] = $field;

					$request->ItemChanges[] = $change;
				}

				$response = $this->ews->UpdateItem($request);
				//echo '<pre>'.print_r($response, true).'</pre>';
			}
		}
	}
	
	function get_attachment_details($attid)
	{
		$request = new EWSType_GetAttachmentType();
		$request->AttachmentIds->AttachmentId->Id = $attid;
		$response = $this->ews->GetAttachment($request);
		
		return isset($response->ResponseMessages->GetAttachmentResponseMessage->Attachments->FileAttachment) ? 
			$response->ResponseMessages->GetAttachmentResponseMessage->Attachments->FileAttachment : null;
	}
	
	function get_mail_details($mail_id)
	{
		$request = new EWSType_GetItemType();
		$request->ItemShape = new EWSType_ItemResponseShapeType();
		$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;
		// You can get the body as HTML, text or "best".
		$request->ItemShape->BodyType = EWSType_BodyTypeResponseType::BEST;

		// Add the body property.
		$body_property = new EWSType_PathToUnindexedFieldType();
		$body_property->FieldURI = 'item:Body';
		$request->ItemShape->AdditionalProperties = new EWSType_NonEmptyArrayOfPathsToElementType();
		$request->ItemShape->AdditionalProperties->FieldURI = array($body_property);

		$request->ItemIds = new EWSType_NonEmptyArrayOfBaseItemIdsType();
		$request->ItemIds->ItemId = array();

		// Add the message to the request.
		$message_item = new EWSType_ItemIdType();
		$message_item->Id = $mail_id;
		$request->ItemIds->ItemId[] = $message_item;

		$response = $this->ews->GetItem($request);
		$maildetails = null;
		
		if (isset($response->ResponseMessages->GetItemResponseMessage->Items->Message)) {
			$maildetails = $response->ResponseMessages->GetItemResponseMessage->Items->Message;
		}

		return $maildetails;
	}

	
	

}
