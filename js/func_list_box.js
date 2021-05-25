function append(fromList, toList, valFld, isReverse) {
	var frmElm = document.getElementById(fromList);
	var toElm = document.getElementById(toList);
	var newValue, newText;
	var selIndex = frmElm.selectedIndex;
	
	if (selIndex>=0) {
		newValue = frmElm.options[selIndex].value;
		newText = frmElm.options[selIndex].text;
		/*
		for(i = 0 ; i < toElm.length ; i++) {
			if (newValue == toElm.options[i].value) {
				alert(newText + ' Already Exists in the List');
				return false;
			}
		}
		*/

		var newOpt1 = new Option(newText, newValue);

		toElm.options[toElm.length] = newOpt1;
	
		toElm.selectedIndex =toElm.length-1;
		
		//change by masud
		/*
		frmElm.options[selIndex] = null;
		if (frmElm.length > 0) {
			frmElm.selectedIndex = selIndex == 0 ? 0 : selIndex - 1;
		}
		*/
		//end change
		frmElm.options[selIndex] = null;
		if (frmElm.length > 0) {
			frmElm.selectedIndex = selIndex == 0 ? 0 : selIndex - 1;
		}
		
		if(isReverse)
			document.getElementById(valFld).value = calcValue(fromList, valFld);
		else
			document.getElementById(valFld).value = calcValue(toList, valFld);

		//document.getElementById(valFld).value = calcValue(toList, valFld);
	}
	
}

function calcValue(toList, valFld) {
	var retVal = '';
	var toElm = document.getElementById(toList);

	for(i = 0 ; i < toElm.length ; i++) {
		var val = toElm.options[i].value;
		if(val.length > 0) retVal += val + ',';
	}
	
	return retVal;
}

function remove(tSel, valFld) {
	var theSel=document.getElementById(tSel);
	var selIndex = theSel.selectedIndex;
	if(selIndex != -1) {
		if(theSel.options[selIndex].selected)	{
			theSel.options[selIndex] = null;
		}

		if (theSel.length > 0) {
			theSel.selectedIndex = selIndex == 0 ? 0 : selIndex - 1;
		}
		
		document.getElementById(valFld).value = calcValue(tSel, valFld);
	}
}
