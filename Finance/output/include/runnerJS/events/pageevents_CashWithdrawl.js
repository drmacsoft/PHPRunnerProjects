
Runner.pages.PageSettings.addPageEvent('CashWithdrawl',Runner.pages.constants.PAGE_ADD,"afterPageReady",function(pageObj,proxy,pageid,inlineRow,inlineObject){var ctrlCleintNo=Runner.getControl(pageid,'ClientNo');ctrlCleintNo.setDisabled();var ctrlAccountName=Runner.getControl(pageid,'AccountName');var ctrlDecription=Runner.getControl(pageid,'Description');var ctrlAmount=Runner.getControl(pageid,'Amount');var ctrlAccountNumber=Runner.getControl(pageid,'AccountNumber');function func(){ctrlDecription.setValue('Withdrawl of['+ctrlAmount.getValue()+'] for ['+ctrlAccountName.getValue()+' - '+ctrlAccountNumber.getValue()+']');};ctrlAccountName.on('keyup',func);ctrlAmount.on('keyup',func);ctrlAccountNumber.on('keyup',func);ctrlAmount.on('change',func);ctrlAccountName.on('change',func);ctrlAccountNumber.on('change',func);;});Runner.pages.PageSettings.addPageEvent('CashWithdrawl',Runner.pages.constants.PAGE_EDIT,"afterPageReady",function(pageObj,proxy,pageid,inlineRow,inlineObject){var ctrlTransactionStatus=Runner.getControl(pageid,'TransactionStatus');if((ctrlTransactionStatus.getValue()=='Cancelled')||(ctrlTransactionStatus.getValue()=='Approved')){ctrlTransactionStatus.setDisabled();};});