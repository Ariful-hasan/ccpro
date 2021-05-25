<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title><?php echo !empty($client->Mobile) ? $client->Mobile : "Customer Information..."?></title>
</head>
<body>
   <div class="col-md-12 col-lg-12">
       <div class="panel panel-default">
           <div class="panel-heading">
               <div class="panel-title pull-right">
                   <form action="<?php echo $this->url('task=lanka&act=crm') ?>" class="form form-horizontal" method="post">
                       <div class="form-group">
                           <label for="mobile" class="control-label col-md-2">Mobile</label>
                           <div class="col-md-7">
                               <input type="text" id="mobile" maxlength="14" class="form-control" name="mobile" value="">
                           </div>
                           <div class="col-md-3">
                               <button type="submit" class="btn btn-success btn-sm">Search</button>
                           </div>
                       </div>
                   </form>
               </div>
               <div class="clearfix"></div>
           </div>

           <div class="panel-body" style="padding: 0">
               <div class="table-responsive">
                   <table class="table table-bordered table-hover small"  style="margin-bottom: 0">
                       <tr>
                           <th>Name</th><td><?php echo !empty($client->InvestorName) ? $client->InvestorName : '' ?></td>
                           <th>Mobile</th><td>
                               <?php if (!empty($client->Mobile)) : ?>
                                   <a href="<?php echo $this->url('task=lanka&act=sms-template-options&mobile='.urlencode('01521508655').'&callid='.urlencode($callid).'&skill_id='.urlencode($skill_id)) ?>"
                                      data-w="800" data-h="400" class="btn btn-primary btn-sm lightboxWIFR cboxElement">
                                       <?php echo !empty($client->Mobile) ? $client->Mobile : '' ?>
                                   </a>
                               <?php endif; ?>
                           </td>
                       </tr>
                       <tr>
                           <th>Email</th>
                           <td>
                               <?php if (!empty($client->Email)) : ?>
                                   <?php
                                       $param ="email=".$client->Email;
                                       $param = urlencode($client->Email);
                                   ?>
                                   <a href="<?php echo $this->url("task=lanka&act=create&email=".urlencode($client->Email)."&name=".urlencode($client->InvestorName)) ?>"
                                      data-w="800" data-h="500" class="btn btn-info btn-sm lightboxWIFR cboxElement">
                                       <?php echo !empty($client->Email) ? $client->Email : '-' ?>
                                   </a>
                               <?php endif; ?>
                           </td>
                           <th>Father's Name</th>
                           <td><?php echo !empty($client->father_Name) ? $client->father_Name : '-' ?></td>
                       </tr>
                       <tr>
                           <th>Mothers's Name</th><td><?php echo !empty($client->mother_Name) ? $client->mother_Name : '-' ?></td>
                           <th>Date of Birth</th><td><?php echo !empty($client->birth_Dt) ? $client->birth_Dt : '-' ?></td>
                       </tr>
                       <tr>
                           <th>Last Trading</th><td><?php echo !empty($client->LastTradeDate) ? $client->LastTradeDate : '' ?></td>
                       </tr>
                   </table>
               </div>
           </div>
       </div>

       <div class="panel panel-default">
           <div class="panel-heading">Bo Information</div>
           <div class="panel-body" style="padding: 0">
               <div class="table-responsive">
                   <table class="table table-bordered table-hover small"  style="margin-bottom: 0">
                       <tr>
                           <th>Available balance</th>
                           <td><?php echo !empty($client->AvailableBalance) ? $client->AvailableBalance : '-' ?></td>
                           <th> Immature balance</th>
                           <td><?php echo !empty($client->ImmatureBalance) ? $client->ImmatureBalance : '-' ?></td>
                       </tr>
                       <tr>
                           <th>Purchase Power</th><td><?php echo !empty($client->PP) ? $client->PP : '-' ?></td>
                           <th>Ledger Balance</th><td><?php echo !empty($client->LedgerBalance) ? $client->LedgerBalance : '-' ?></td>
                       </tr>
                       <tr>
                           <th>Current IPO Application Status</th>
                           <td><?php echo !empty($client->IPOStatus) ? $client->IPOStatus : '-' ?></td>
                           <th> Name, Email of Relationship Manager (RM)</th>
                           <td> <?php echo !empty($client->RMName) ? $client->RMName : '-' ?> ,
                               <?php echo !empty($client->RMEmail) ? $client->RMEmail : '-' ?>
                           </td>
                       </tr>
                       <tr>
                           <th>Last Trading</th>
                           <td><?php echo !empty($client->LastTradeDate) ? $client->LastTradeDate : '-' ?></td>
                           <th>Marginable equity</th>
                           <td><?php echo !empty($client->MarginableEquity) ? $client->MarginableEquity : '-' ?></td>
                       </tr>
                       <tr>
                           <th>Non Marginable equity</th>
                           <td><?php echo !empty($client->NonMarginableEquity) ? $client->NonMarginableEquity : '-' ?></td>
                           <th>Total equity</th>
                           <td><?php echo !empty($client->Equity) ? $client->Equity : '-' ?></td>
                       </tr>
                       <tr>
                           <th>Accrued interest</th>
                           <td><?php echo !empty($client->AccruedInterest) ? $client->AccruedInterest : '-' ?></td>
                           <th> Unrealized gain loss</th>
                           <td><?php echo !empty($client->UnrealizeGainLoss) ? $client->UnrealizeGainLoss : '-' ?></td>
                       </tr>
                       <tr>
                           <th> Loan ratio</th><td><?php echo !empty($client->LoanRatio) ? $client->LoanRatio : '-' ?></td>
                           <th> Exposure</th><td><?php echo !empty($client->exposure) ? $client->exposure : '-' ?></td>
                       </tr>
                       <tr>
                           <th>  Last deposited amount and date</th>
                           <td>
                               <?php echo !empty($client->LastDeposit) ? $client->LastDeposit : '-' ?> at
                               <?php echo !empty($client->LastDepositDate) ? $client->LastDepositDate : '-' ?>
                           </td>
                           <th>  Last withdrawal amount and date</th>
                           <td>
                               <?php echo !empty($client->LastWithdrawal) ? $client->LastWithdrawal : '-' ?> at
                               <?php echo !empty($client->LastWithdrawalDate) ? $client->LastWithdrawalDate : '-' ?>
                           </td>
                       </tr>
                   </table>
               </div>
           </div>
       </div>


       <div class="panel panel-default">
           <div class="panel-heading">Service Availed</div>
           <div class="panel-body" style="padding: 0">
               <div class="table-responsive">
                   <table class="table table-bordered table-hover small"  style="margin-bottom: 0">
                       <tr>
                           <th>Email Service</th><td><?php echo !empty($client->ServiceAvailedEmail) ? $client->ServiceAvailedEmail : '-' ?></td>
                           <th>SMS Service</th><td> <?php echo !empty($client->ServiceAvailedSMS) ? $client->ServiceAvailedSMS : '' ?></td>
                           <th>Market data service</th><td>-</td>
                       </tr>
                       <tr>
                           <th>Ibroker Service</th><td>-</td>
                           <th>TradeXpress App</th><td>-</td>
                       </tr>
                   </table>
               </div>
           </div>
       </div>

       <div class="panel panel-default">
           <div class="panel-heading">Previous Inquiry</div>
           <div class="panel-body" style="padding: 0">
               <div class="table-responsive">
                   <table class="table table-bordered table-hover small"  style="margin-bottom: 0">
                       <tr>
                           <th class="text-center">Date</th>
                           <th class="text-center">Cli</th>
                           <th class="text-center">Agent ID</th>
                           <th class="text-center">Note</th>
                       </tr>
                       <?php if (!empty($dispositions)): ?>
                           <?php foreach ($dispositions as $disposition){ ?>
                              <tr>
                                  <td class="text-center"><?php echo date('Y-m-d H:i:s', $disposition->tstamp)?></td>
                                  <td class="text-center"><?php echo $disposition->cli ?></td>
                                  <td class="text-center"><?php echo $disposition->agent_id ?></td>
                                  <td class="text-left"><?php echo $disposition->note ?></td>
                              </tr>
                           <?php } ?>
                       <?php else: ?>
                           <tr>
                                <td colspan="4" class="text-danger text-center">No record Found</td>
                           </tr>
                       <?php endif; ?>
                   </table>
               </div>
           </div>
       </div>
   </div>
   <script src="assets/js/jquery-1.11.2.min.js" type="text/javascript"></script>
   <link href="js/lightbox/colorbox.css" rel="stylesheet" media="screen">
   <script src="js/lightbox/jquery.colorbox-min.js" type="text/javascript"></script>
   <script src="js/gsscript.js"></script>
   <script>
       $(function () {
           try {
               $("#MainLoader").remove();
           } catch (e) {
           }
       });
   </script>

</body>
</html>