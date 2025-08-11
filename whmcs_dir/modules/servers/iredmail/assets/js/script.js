  var policyArry =  {
        "public" : "Unrestricted",
        "domain" : "Users under same domain",
        "subdomain":"Users under same domain and its sub-domains",
        "membersonly" : "Members",
        "moderatorsonly" :"Moderators",
        "membersandmoderatorsonly" : "Members and moderators",
    };
 
jQuery(document).ready(function () {
 
    getEmailList();
    //getEmailalias();
    let searchParams = new URLSearchParams(window.location.search);
    var check = searchParams.has('page');
    if(check == false){
        var currenturl = jQuery(location).attr('href')+'&page=managedomain';
        window.history.pushState({path:currenturl},'',currenturl);
    }
    searchParams = new URLSearchParams(window.location.search);
    var pageSection = searchParams.get('page');

    if(pageSection == 'manageemail' ){
        var pageSection2 = 'managedomain';
       // var pageSection3 = 'manageemailalias';
    }else if(pageSection == 'managedomain'){
        var pageSection2 = 'manageemail';
        //var pageSection3 = 'manageemailalias';
    }/*else if(pageSection == 'manageemailalias'){
        var pageSection2 = 'manageemail';
       // var pageSection3 = 'managedomain';
    }*/
    jQuery("." + pageSection).slideDown("slow");
    jQuery("." + pageSection2).slideUp("slow");
    //jQuery("." + pageSection3).slideUp("slow");

    //Quota price upgrade  conversion
   jQuery("#quotachange").on("input", function() {
       var quotaSize = jQuery(this).val();
       if(quotaSize == 0){
            jQuery('#btn_upgrade_quota').prop('disabled', true);
       }else{
            jQuery('#btn_upgrade_quota').prop('disabled', false);
       }
       var finalPrice = defaultQuotaPrice * quotaSize;
       jQuery('#upgrade_price').text(finalPrice);
    });


    jQuery('#inputEmail3').keyup(function() {
      
      var email = jQuery(this).val();
      jQuery(".btn_addemail").prop("disabled", true);
      if(email == ''){
        jQuery("#valid-msg").text('');
      }else{
        var ext = jQuery('.select-dname').text();
      
          email = email+ext;
          
          var checkvalid = emailvalidation(email,'mail');
          
          if(checkvalid == 'valid'){
            jQuery("#valid-msg").text('');
            jQuery(".btn_addemail").prop("disabled", false);
          }else{
            jQuery(".btn_addemail").prop("disabled", true);
            jQuery('#valid-msg').show();
            jQuery("#valid-msg").text('Please enter valid mail address!');
          }
      }
      
    }); 

    jQuery('#email-name-alias').keyup(function() {
       
      var email = jQuery(this).val();
       
      if(email == ''){
        jQuery("#valid-msg-alias").text('');
      }else{
        var ext = jQuery('.selected-dname').text();
      
          email = email+ext;
          
          var checkvalid = emailvalidation(email,'mail');
          
          if(checkvalid == 'valid'){
            jQuery("#valid-msg-alias").text('');
            
          }else{
            
            jQuery('#valid-msg-alias').show();
            jQuery("#valid-msg-alias").text('Please enter valid mail address!');
          }
      }
      
    });

    jQuery('#d-name').keyup(function() {
        jQuery("#d_name_error").text('');          
        var email = jQuery(this).val().trim();
       
        if(email.length > 0){
            var checkvalid = emailvalidation(email,'domain');

            if(checkvalid == 'valid'){
                jQuery("#d_name_error").text('');
                jQuery(".domain_add_btn").prop("disabled", false);
            }else{
                jQuery(".domain_add_btn").prop("disabled", true);
                jQuery('#d_name_error').show();
                jQuery("#d_name_error").text('Please enter valid domain name!');
            }
        }
     
    }); 

    jQuery('#targetdomain-name').keyup(function() {
        jQuery("#targetdomain_error").text('');          
        var email = jQuery(this).val().trim();
       
        if(email.length > 0){
            var checkvalid = emailvalidation(email,'domain');

            if(checkvalid == 'valid'){
                jQuery("#targetdomain_error").text('');
                jQuery(".targetdomain_add_btn").prop("disabled", false);
            }else{
                jQuery(".targetdomain_add_btn").prop("disabled", true);
                jQuery('#targetdomain_error').show();
                jQuery("#targetdomain_error").text('Please enter valid domain name!');
            }
        }
     
    });

    jQuery('#targetdomain').keyup(function() {
        jQuery("#targetdomain_error").text('');          
        var email = jQuery('#d-name').val().trim();
       
        if(email.length > 0){
            var checkvalid = emailvalidation(email,'domain');

            if(checkvalid == 'valid'){
                jQuery("#targetdomain_error").text('');
                jQuery(".targetdomain_add_btn").prop("disabled", false);
            }else{
                jQuery(".targetdomain_add_btn").prop("disabled", true);
                jQuery('#targetdomain_error').show();
                jQuery("#targetdomain_error").text('Please enter valid domain name!');
            }
        }
    });

    jQuery('.btn_modalreset').click(function(){
        jQuery("#email-form").trigger('reset');
        jQuery("#emailalias-form").trigger('reset');
        jQuery("#error-emailalias").html('');
    });  

});

function selectdomain(that) {
  var domainselected =  that.value;
  jQuery('.select-dname').html('@'+domainselected);
};
function selectdomainAlias(that) {
  var domainselected =  that.value;
  jQuery('.selected-dname').html('@'+domainselected);
};

function getdomainlist(){
    jQuery('#domain_list tbody').html('<tr><td colspan="3"><div style="text-align:center;color:red;font-size:18px"><i class="fas fa-spinner fa-pulse"></i></div></td><tr>');
    jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=get_domain_list',
        success: function (response) {
            // jQuery('#doaminalias-loader').hide();
            var domainAlias;
            var myArray = JSON.parse(response);
          
            var domainlist = '';
            jQuery.each(myArray, function (key, val) {
                
                var domainAlias = '';
                if(val.domainalias.length > 0){
                   
                    domainAlias =  '<div id="alias_list" style="display: block;">'+val.domainalias[0]['target_domain']+'<span style="margin-left: 10px;color:#d9534f;cursor:pointer" alias="'+domainAlias+'" domain="'+key+'"  title="Delete Alias" onclick="deldomainalias(this)"><i class="fas fa-trash-alt"></i></span></div>'; 
                   //domainAlias = val.domainalias[0]['target_domain'];
                }
                
                domainlist += '<tr><td>'+key+domainAlias+'</td><td>'+val.emailcount+'</td><td><span   class="manage-d" title="Delete" onclick="deldomain(this,\''+key+ '\')"><i class="fas fa-trash-alt"></i></span><span title="Manage Catch-All"class="manage-catchall" onclick="catchAll(this,\''+key+ '\')"><i class="fas fa-envelope-open-text"></i></span><span   class="manage-alias" title="Domain Alias" onclick="addalias(this,\''+key+ '\')"><i class="fas fa-at"></i></span></td></td></tr>';
            });
            jQuery('#domain_list tbody').html(domainlist);
        }
    });
}


function deldomainalias(that){
    //var targetDomain = jQuery(that).attr("alias");
    var aliasDomain = jQuery(that).attr("domain");
    
      jQuery.confirm({
        title: 'Confirm!',
        content: 'Are you sure to delete this domain alias?',
        buttons: {
            confirm: function () {
                jQuery('#error-del').html('<span style="font-size:24px;color:#bd2130"><i class="fas fa-spinner fa-pulse"></i></span>');
                jQuery.ajax({
                type: 'post',
                url: '',
                data: 'ajaxcall=true&activity=del_alias_domain&domain='+aliasDomain,
                success: function (response) {
                    if(response == 'success'){
                        var status = '<div class="alert alert-success" role="alert">Domain alias deleted successfully!</div>';
                    }else{
                        var status = response;
                    }
                    jQuery('#error-del').html(status);
                    jQuery('#error-del').fadeIn('slow').delay(5000).hide(0);
                    getdomainlist();
                }
            });

                },
            cancel: function () {
               // jQuery.alert('Canceled!');
            },
        }
    });
}

jQuery(document).on("click", "#btn-email-ac", function(event){
    var dnameVal = jQuery('#existdomain').val();
    jQuery('.select-dname').html('@'+dnameVal);
    jQuery('.selected-dname').html('@'+dnameVal);
});

function upgradequota(that){
    jQuery('#upgradequotamodal').modal('show');
    jQuery('#message-changequota').html('');
    var mailname = jQuery(that).attr("mail");
    var domainname = jQuery(that).attr("domain");
    jQuery('#chnge-mail-select-quota').text(mailname);
    jQuery('#quota_chnge_mail').val(mailname);
    jQuery('#domain_quotachnge').val(domainname);
}

function quotaupgrade(that){
    jQuery('#message-changequota').html('');
    var formData = jQuery('#change-quota').serialize();
    jQuery(that).prop('disabled', true);
    jQuery(that).html('<span style="margin-right:5px"><i class="fas fa-spinner fa-pulse"></i></span>Loading...');
    jQuery.ajax({
            type: 'post',
            url: '',
            data: 'ajaxcall=true&activity=upgradequotasize&'+formData,
            success: function (response) {
               
                var myArray = JSON.parse(response);
                 
                jQuery(that).html('Upgrade');
                jQuery(that).prop('disabled', false);
                if(myArray.code == 'success'){
                    jQuery('#message-changequota').html('<div class="alert alert-success" role="alert">'+myArray.msg+'</div>');
                }else{
                    jQuery('#message-changequota').html('<div class="alert alert-danger" role="alert">'+myArray.msg+'</div>');
                }
               // jQuery('#addemailmodal').modal('hide'); 
            }
        });
} 

function overidequota(that,invoiceId){
    jQuery('#message-changequota').html('');
    var formData = jQuery('#change-quota').serialize();
    
   jQuery(that).prop('disabled', true);
    jQuery(that).html('<span style="margin-right:5px"><i class="fas fa-spinner fa-pulse"></i></span>Loading...');
    jQuery.ajax({
            type: 'post',
            url: '',
            data: 'ajaxcall=true&activity=overridequotasize&invoiceid='+invoiceId+'&'+formData,
            success: function (response) {
                 
                var myArray = JSON.parse(response);
                
                jQuery(that).html('Upgrade');
                jQuery(that).prop('disabled', false);
                if(myArray.code == 'success'){
                    jQuery('#message-changequota').html('<div class="alert alert-success" role="alert">'+myArray.msg+'</div>');
                }else{
                    jQuery('#message-changequota').html('<div class="alert alert-danger" role="alert">'+myArray.msg+'</div>');
                } 

               // jQuery('#addemailmodal').modal('hide'); 
            }
        });
}

function mailsetting (that,mailname) {
    jQuery('#manageemailmodal').modal('show');
    var mailname = jQuery(that).attr("mail");
    var domainname = jQuery(that).attr("domain");
    jQuery('#mailaddress_chnge').val(mailname);
    jQuery('#chnge-mail-select').text(mailname);
    jQuery('#domain_chnge').val(domainname);
    jQuery('#error-change').html('');
    //$('#change-password ').reset();
    jQuery('#change_mailpassword').val('');
    jQuery('#change_mailcfpassword').val('');
}

function catchAll(that,domain){
   jQuery("#catchallmodal").modal("show");
   jQuery("#catchallmodal-d").html('');
   jQuery("#catch_domain").text(domain);
   jQuery("#catch_domain_selected").val(domain);
   getDomainCatchAll(domain);
}

function getDomainCatchAll(domain){
    jQuery("#catachall_mail").html('');
    jQuery("#catchallmodal-loader").show();
    
    jQuery(".catch_add_btn").prop('disabled', true);
    jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=get_domain_catch&domain='+domain,
        success: function (response) {
             
            if(response != 'none'){
                var myArray = JSON.parse(response);
           
                var selectedmail = myArray.selectedmail;
                var mailListValue = '';
                mailListValue = '<option value="none">None</option>';
                if(myArray.list.length > 0){
                    jQuery.each(myArray.list, function (key, val) {
                        
                       if(selectedmail == val.username){
                            var selected = 'selected';
                        }else{
                            var selected = '';
                        }
                        
                        mailListValue += '<option value="'+val.username+'" domain="'+val.domain+'" '+selected+'>'+val.username+'</option>';
                    });
                    jQuery('.catch_add_btn').prop('disabled', false);
                }
            }else{
                mailListValue = '<option >You have not any mailaddress! Please add mailaddress first.</option>';
            } 
           
            jQuery("#catachall_mail").html(mailListValue); 
            jQuery("#catchallmodal-loader").hide();
        }
    });
}

function catchalladd(that){
    var formData = jQuery("#catchallmodal-form").serialize();
    jQuery("#catchallmodal-loader").show();
    jQuery("#catchallmodal-d").html('');

    jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=add_domain_catch&'+formData,
        success: function (response) {
             
           jQuery("#catchallmodal-loader").hide();
            if(response == 'success'){
                var status = '<div class="alert alert-success" role="alert">Domain catch all set successfully!</div>';
              
                setTimeout(function(){ jQuery("#catchallmodal").modal("hide");}, 5000);     
            }else{
                var status = response;
            }
            jQuery("#catchallmodal-d").html(status);
        }
    });
}

function addmailalias (that) {
    jQuery('#setmailaliasmodal').modal('show');
    var mailName = jQuery(that).attr("mail");
    var domainName = jQuery(that).attr("domain");
    jQuery('#mailaddress_alias').val(mailName);
    jQuery('#mail_alias_dname').val(domainName);
    jQuery('#mailaddress_name').text(mailName);
    jQuery('#maildomain_name').text(domainName);
    getmailalias(mailName);
    /* old code**/
    //jQuery('#mailaddress_chnge').val(mailname);
    //jQuery('#chnge-mail-select').text(mailname);
    //jQuery('#domain_chnge').val(domainname);
    //jQuery('#error-change').html('');
    //$('#change-password ').reset();
    //jQuery('#change_mailpassword').val('');
    //jQuery('#change_mailcfpassword').val('');
}

function updatedata(that,pw, cpw){
    
    jQuery('.cfameerr, .passworderr').remove();
    if (!jQuery('#' + pw).val()) {
        jQuery('#' + pw).focus().after('<p class="passworderr">Password is required!</p>');
        return false;
    } else if (!jQuery('#' + pw).val().match(/^(?=.*?[a-z])(?=.*?[0-9]).{8,}$/)) {
        jQuery('#' + pw).focus().after('<p class="passworderr">Password is weak!, &nbsp; Password must be at least 8 characters in length and include 1 digit. For example: test1234</p>');
        return false;
    } else if (!jQuery('#' + cpw).val()) {
        jQuery('#' + cpw).focus().after('<p class="passworderr">Confirm Password is required!</p>');
        return false;
    } else if (jQuery('#' + pw).val() != jQuery('#' + cpw).val()) {
        jQuery('#' + cpw).focus().after('<p class="cfameerr">Passwords must match!</p>');
        return false;
    } else {
         
        jQuery(that).prop('disabled', false);
        var formData = jQuery('#change-password').serialize();
        jQuery(that).html('<span style="margin-right:5px"><i class="fas fa-spinner fa-pulse"></i></span>Loading...');
        jQuery.ajax({
            type: 'post',
            url: '',
            data: 'ajaxcall=true&activity=changepassword&'+formData,
            success: function (response) {
                
                jQuery(that).html('update');
                jQuery(that).prop('disabled', false);
                if(response == 'success'){
                   jQuery('#error-change').html('<div class="alert alert-success" role="alert">Password changed successfully!</div>');
                   // getEmailList();
                   /* setTimeout(function(){
                        location.reload(); 
                     }, 3000);*/
                }else{
                   jQuery('#error-change').html('<div class="alert alert-danger" role="alert"> '+response+'</div>');
                }
               // jQuery('#addemailmodal').modal('hide'); 
            }
        });
    }
}

function emailstatus(that){
    var status = jQuery(that).attr("status");
    var email_user = jQuery(that).attr("mail");
    var currentStatus;
    if(status == 0){
        currentStatus = 'enable';
        status = 1;
    }else{
        currentStatus = 'disable';
        status = 0;
    }

    jQuery.confirm({
        title: 'Confirm!',
        content: 'Are you sure to '+currentStatus+' this Email Account?',
        buttons: {
            confirm: function () {
                jQuery('#error-email').html('<span style="color:red;font-size:25px"><i class="fas fa-spinner fa-pulse"></i></span>');

                jQuery.ajax({
                    type: 'post',
                    url: '',
                    data: {ajaxcall : true,activity:'accountenable_disable','email_user':email_user,'email_status':status},
                   
                    success: function (response) {
                        
                        if(response == 'success'){
                            jQuery('#error-email').html('<div class="alert alert-success" role="alert">Status updated successfully!</div>');
                          
                         
                        }else{
                            jQuery('#error-email').html('<div class="alert alert-danger" role="alert">'+response+'</div>');
                        }
                        setTimeout(function(){
                           jQuery('#error-email').html('');
                           getEmailList();
                        }, 3000);
                    }
                });

            },
            cancel: function () {
               // jQuery.alert('Canceled!');
            },
        }
    });

}

function delEmail(that){
    var mailname = jQuery(that).attr("mail");
    var dname = jQuery(that).attr("domain");
        jQuery.confirm({
        title: 'Confirm!',
        content: 'Are you sure to delete this Email Account?',
        buttons: {
            confirm: function () {

                jQuery("#error-email").html('<span style="color:red;font-size:25px"><i class="fas fa-spinner fa-pulse"></i></span>');
                jQuery(that).prop('disabled', true);
                  jQuery.ajax({
                    type: 'post',
                    url: '',
                    data: {ajaxcall : true,activity:'maildelete','mail':mailname,'domain':dname},
                   
                    success: function (response) {
                        
                        if(response == 'success'){
                            jQuery('#error-email').html('<div class="alert alert-success" role="alert">Email deleted successfully!</div>');
                            /*
                            setTimeout(function(){
                                location.reload(); 
                            }, 3000);*/
                        }else{
                            jQuery('#error-email').html('<div class="alert alert-danger" role="alert">'+response+'</div>');
                        }
                        setTimeout(function(){
                           jQuery('#error-email').html('');
                           getEmailList();
                        }, 3000);
                    }
                 });
            },
            cancel: function () {
                //jQuery.alert('Canceled!');
            },
        }
    });
}

function emailImap(that){
    var status = jQuery(that).attr("imapstatus");
    var email_user = jQuery(that).attr("mail");
    var currentStatus;
    if(status == 0){
        currentStatus = 'enable';
        status = 1;
    }else{
        currentStatus = 'disable';
        status = 0;
    }

    jQuery.confirm({
        title: 'Confirm!',
        content: 'Are you sure to '+currentStatus+' IMAP for email?',
        buttons: {
            confirm: function () {
                jQuery('#error-email').html('<span style="color:red;font-size:25px"><i class="fas fa-spinner fa-pulse"></i></span>');
                jQuery.ajax({
                type: 'post',
                url: '',
                data: {ajaxcall : true,activity:'enable_disable_imap','email_user':email_user,'imap_status':status},

                success: function (response) {
                    
                    if(response == 'success'){
                        jQuery('#error-email').html('<div class="alert alert-success" role="alert">Email IMAP status updated successfully!</div>');
                      
                        
                    }else{
                        jQuery('#error-email').html('<div class="alert alert-danger" role="alert">'+response+'</div>');
                    }
                    setTimeout(function(){
                       jQuery('#error-email').html('');
                       getEmailList();
                    }, 3000);
                }
                });

            },
            cancel: function () {
               // jQuery.alert('Canceled!');
            },
        }
    });
}

function popEmail(that){
    var status = jQuery(that).attr("popstatus");
    var email_user = jQuery(that).attr("mail");
    var currentStatus;
    if(status == 0){
        currentStatus = 'enable';
        status = 1;
    }else{
        currentStatus = 'disable';
        status = 0;
    }
    jQuery.confirm({
        title: 'Confirm!',
        content: 'Are you sure to '+currentStatus+' POP for email?',
        buttons: {
            confirm: function () {
                jQuery('#error-email').html('<span style="color:red;font-size:25px"><i class="fas fa-spinner fa-pulse"></i></span>');
                jQuery.ajax({
                    type: 'post',
                    url: '',
                    data: {ajaxcall : true,activity:'enable_disablePop','email_user':email_user,'pop_status':status},
                   
                    success: function (response) {
                        
                        if(response == 'success'){
                            jQuery('#error-email').html('<div class="alert alert-success" role="alert">Email POP status updated successfully!</div>');
                        }else{
                            jQuery('#error-email').html('<div class="alert alert-danger" role="alert">'+response+'</div>');
                        }
                        setTimeout(function(){
                           jQuery('#error-email').html('');
                           getEmailList();
                        }, 3000);
                    }
                });

            },
            cancel: function () {
               // jQuery.alert('Canceled!');
            },
        }
    });
}

function addalias(that,domain){
    jQuery('#domainaliasmodal').modal('show');
    //var mailaddress = $(that).attr("mail");
    //var domain = $(that).attr("domain");
    jQuery('#error-domainalias').html('');
    jQuery('#targetdomain-name').val('');

    jQuery('.domain_name_alias').val(domain);
    jQuery('#domain_alias_dname').text(domain);
   // jQuery('#doaminalias-loader').show();
    //getdomainalias(domain);
}


function setmailaliasadd(that){
    var formData = jQuery("#setmailalias-form").serialize();
    jQuery('#setmailalias-loader').show();
    jQuery('#error-setmailalias').html('');
    
    jQuery('.alias_mailadd_btn').prop('disabled', true);
    jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=set_alias_mail&'+formData,
        success: function (response) {
            jQuery('#setmailalias-loader').hide();
            jQuery('.alias_mailadd_btn').prop('disabled', false);
            if(response == 'success'){
                jQuery('#error-setmailalias').html('<div class="alert alert-success" role="alert"> Alias mail set successfully!</div>');
                setTimeout(function(){
                    document.getElementById('emailaliasmodalclose').click();
                    jQuery('#error-setmailalias').html('');
                    getEmailalias();
                }, 3000);
            }else{
                jQuery('#error-setmailalias').html('<div class="alert alert-danger" role="alert">'+response+'</div>');
               
            }
          
        }
    });
}

function getmailalias(mailname){
    jQuery("#setmailalias-loader").show();
    jQuery("#error-setmailalias").html('');
    jQuery("#mailalias-name").val(''); 

    jQuery('.alias_mailadd_btn').prop('disabled', true);
    jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=get_mail_alias&mailaddress='+mailname,
        success: function (response) {
             
            jQuery("#setmailalias-loader").hide();
            jQuery('.alias_mailadd_btn').prop('disabled', false);
            jQuery("#mailalias-name").val(response);  
        }
    });
}
/*function getdomainalias(domain){
    jQuery(".targetdomain_add_btn").prop('disabled', true);
    jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=get_alias_domain&domain='+domain,
        success: function (response) {
            jQuery('#doaminalias-loader').hide();
            jQuery(".targetdomain_add_btn").prop('disabled', false);
            var myArray = JSON.parse(response);
             
            if(myArray.length > 0){
                
                jQuery('#targetdomain-name').val(myArray[0].target_domain); 
            }
        }
    });

}
*/
/*function editEmailalias(that){
    var mailaddress = $(that).attr("mail");
    var domain = $(that).attr("domain");
    var d_name = $(that).attr("name");
    var policy = $(that).attr("policy");
   
    jQuery('#editmailaliasmodal').modal('show');
    jQuery('#edit_name').val(d_name);
    jQuery('.edit_mailaddress_head').text(mailaddress);
    jQuery('#edit_mailaddress_alias').val(mailaddress);
    jQuery('#edit_domain_name_alias').val(domain);
    jQuery("input[name=policy][value='"+policy+"']").prop("checked",true);
}
function emailaliasupdate(that){
    var formData = jQuery("#editemailalias-form").serialize();
    jQuery('#emailaliasupdate-loader').show();
    jQuery('#error-emailaliasupdate').html('');
    jQuery('.alias_update_btn').prop('disabled', true);
    jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=update_mail_alias&'+formData,
        success: function (response) {
            jQuery('#emailaliasupdate-loader').hide();
            jQuery('.alias_update_btn').prop('disabled', false);
            
            if(response == 'success'){
                jQuery('#error-emailaliasupdate').html('<div class="alert alert-success" role="alert"> Alias mail update successfully!</div>');
              
            }else{
                jQuery('#error-emailaliasupdate').html('<div class="alert alert-danger" role="alert">'+response+'</div>');
            }
            setTimeout(function(){
                jQuery('#error-emailaliasupdate').html('');
                document.getElementById('mailaliasmodalclose').click();
                getEmailalias();
            }, 4000);
        }
    });
}*/

/*function getEmailalias(){
    jQuery("#emailalias-loader").show();
    jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=get_alias_email',
        success: function (response) {
            jQuery("#emailalias-loader").hide();
            var myArray = JSON.parse(response);
            
            var mailaliasvalue = '';
            jQuery.each(myArray, function (key, val) {
                var accesspolicy = policyArry[val.accesspolicy];
                var displayName = val.name;
                mailaliasvalue += "<tr><td>"+val.name+"</td><td>"+val.address+"</td><td>"+val.domain+"</td><td>"+accesspolicy+"</td><td><span   domain="+val.domain+" mail="+val.address+" name='"+displayName+"' policy="+val.accesspolicy+"  style='cursor: pointer;margin-right:5px;' onclick='editEmailalias(this)''><i class='fas fa-edit'></i></span><span   domain="+val.domain+" mail="+val.address+" style='cursor: pointer;color:#dc3545' onclick='delEmailalias(this)''><i class='fas fa-trash-alt'></i></span></td></tr>";
            });
            
            jQuery("#alias_tbl tbody").html(mailaliasvalue); 
        }
    });

}*/
 

/*function emailAlias(that){
    jQuery('#emailaliasmodal').modal('show');
    var mailaddress = $(that).attr("mail");
    var domain = $(that).attr("domain");
    jQuery('#mailaddress_alias').val(mailaddress);
    jQuery('.domain_name_alias').val(domain);
    jQuery('.selected-dname').text('@'+domain);
    jQuery('#error-emailalias').html('');
   // getEmailalias(domain,mailaddress);

}*/

/*function emailaliasadd(that){
    var formData = jQuery("#emailalias-form").serialize();
    jQuery('#emailaliasadd-loader').show();
    jQuery('#error-emailalias').html('');
    
    jQuery('.alias_add_btn').prop('disabled', true);
    jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=set_alias&'+formData,
        success: function (response) {
            jQuery('#emailaliasadd-loader').hide();
            jQuery('.alias_add_btn').prop('disabled', false);
            if(response == 'success'){
                jQuery('#error-emailalias').html('<div class="alert alert-success" role="alert"> Alias mail set successfully!</div>');
                setTimeout(function(){
                    document.getElementById('emailaliasmodalclose').click();
                    jQuery('#error-emailalias').html('');
                    getEmailalias();
                }, 3000);
            }else{
                jQuery('#error-emailalias').html('<div class="alert alert-danger" role="alert">'+response+'</div>');
               
            }
          
        }
    });
}
*/

/*function delEmailalias(that){
    var mailaddress = $(that).attr("mail");
    var domain = $(that).attr("domain");
    jQuery.confirm({
        title: 'Confirm!',
        content: 'Are you sure to delete this Alias?',
        buttons: {
            confirm: function () {
                jQuery("#emailalias-loader").show();
                jQuery.ajax({
                    type: 'post',
                    url: '',
                    data: {ajaxcall : true,activity:'mail_alias_delete','mail':mailaddress,'domain':domain},
                   
                    success: function (response) {
                        jQuery("#emailalias-loader").hide();
                        if(response == 'success'){
                            jQuery('#emailalias-msg').html('<div class="alert alert-success" role="alert">Email Alias deleted successfully!</div>');
                        }else{
                            jQuery('#emailalias-msg').html('<div class="alert alert-danger" role="alert">'+response+'</div>');
                        }
                        setTimeout(function(){
                            jQuery('#emailalias-msg').html('');
                            getEmailalias();
                        }, 3000);
                    }
                 });
        },
            cancel: function () {
               // jQuery.alert('Canceled!');
            },
        }
    });
}
*/
function domainaliasadd(that){
    var formData = jQuery("#domainalias-form").serialize();
    jQuery('#doaminalias-loader').show();
    jQuery('#error-domainalias').html('');
    jQuery(".targetdomain_add_btn").prop('disabled', true);
    jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=set_alias_domain&'+formData,
        success: function (response) {
            jQuery('#doaminalias-loader').hide();
            jQuery(".targetdomain_add_btn").prop('disabled', false);
            if(response == 'success'){
                jQuery('#error-domainalias').html('<div class="alert alert-success" role="alert"> Domain alias set successfully!</div>');
                setTimeout(function(){
                  jQuery('#domainaliasmodal').modal('hide');
                  getdomainlist();
                }, 3000);

            }else{
                jQuery('#error-domainalias').html('<div class="alert alert-danger" role="alert">'+response+'</div>');
            }
        }
    });
}

function forwordEmail(that){
    //emailforwordingmodal
    var mailaddress = jQuery(that).attr("mail");
    var domain = jQuery(that).attr("domain");
    jQuery('#emailforwordingmodal').modal('show');
    jQuery('#error-emailforwording').html('');
    jQuery('#mailaddress').val(mailaddress);
    jQuery('#domain_name').val(domain);
    jQuery('#email-forwording').val('');
    jQuery('#emailforwording-loader').show();
    getforwardingmails(mailaddress,domain);
}

function getforwardingmails(mail,domain){
     //getforwardingmail

      jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=getforwardingmail&mail='+mail+'&domain='+domain,
        success: function (response) {
            jQuery('#emailforwording-loader').hide();
            var myArray = JSON.parse(response);
             
            var forwordingvalue = '';
            
            jQuery.each(myArray, function (key, val) {
                forwordingvalue += val.forwarding+",";
            });
            var lastChar = forwordingvalue.slice(-1);
            if (lastChar == ',') {
                forwordingvalue = forwordingvalue.slice(0, -1);
            }

            jQuery('#email-forwording').val(forwordingvalue);
        }
    });
}
function forwordingmailadd(that){
    var formData = jQuery("#emailforwording-form").serialize();
    
    jQuery('#emailforwording-loader').show();
    jQuery('#error-emailforwording').html('');
    jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=set_forwarding&'+formData,
        success: function (response) {
            jQuery('#emailforwording-loader').hide();

            if(response == 'success'){
                jQuery('#error-emailforwording').html('<div class="alert alert-success" role="alert"> Forwardering mail set successfully!</div>');
              
                setTimeout(function(){
                    jQuery('#emailforwordingmodal').modal('hide');
                    getEmailList();
                }, 3000);
            }else{
                jQuery('#error-emailforwording').html('<div class="alert alert-danger" role="alert">'+response+'</div>');
            }
        }
    });

}

function getEmailList(){
     
    jQuery('.email-tbl tbody').html('');
    jQuery('#error-email').html('<span style="color:red;font-size:25px"><i class="fas fa-spinner fa-pulse"></i></span>');
    jQuery.ajax({
        type: 'post',
        url: '',
        data: 'ajaxcall=true&activity=getemail',
        success: function (response) {
          
            var myArray = JSON.parse(response);
            jQuery('#error-email').html('');
          
            var statusclass;
            jQuery.each(myArray, function (key, val) {
                var emailstatus = val.active;
                var imapStatus = val.enableimap;
                var popStatus = val.enablepop3;
                if(emailstatus == 0){
                 statusclass = "#dc3545";
                }else{
                 statusclass = "#1e7e34";
                } 

                if(imapStatus == 0){
                 statusclassimap = "#dc3545";
                }else{
                 statusclassimap = "#1e7e34";
                } 

                if(popStatus == 0){
                 statusclasspop = "#dc3545";
                }else{
                 statusclasspop = "#1e7e34";
                }

                if(defaultPlanType == 'Unlimited'){
                     jQuery('.email-tbl tbody').append('<tr><td>'+val.name+'</td><td style="word-break: break-all;">'+val.username+'</td> <td>('+val.used+'/'+val.quota+')</td><td><span title="Change password"  class="mail-set action_btn" domain="'+val.domain+'" mail="'+val.username+'"onclick="mailsetting(this)" ><i class="fas fa-cogs"></i></span> <span  title="Enable/Disable" class="action_btn" domain="'+val.domain+'"  status="'+emailstatus+'" mail="'+val.username+'" style="color:'+statusclass+';" onclick="emailstatus(this)"><i class="fas fa-power-off"></i></span><span class="action_btn"  title="Delete" domain="'+val.domain+'" mail="'+val.username+'" style="color:#dc3545" onclick="delEmail(this)"><i class="fas fa-trash-alt"></i></span><span class="action_btn"  title="Forward Email" domain="'+val.domain+'" mail="'+val.username+'" onclick="forwordEmail(this)"><i class="fas fa-share-square"></i></span><span class="action_btn" title="Email POP" domain="'+val.domain+'" imapstatus="'+imapStatus+' popstatus="'+popStatus+' "mail="'+val.username+'" style="color:'+statusclasspop+'" onclick="popEmail(this)"><i class="fas fa-envelope-open-text"></i></span><span class="action_btn" title="Email IMAP" domain="'+val.domain+'" imapstatus="'+imapStatus+' popstatus="'+popStatus+' mail="'+val.username+'" style="color:'+statusclassimap+'" onclick="emailImap(this)"><i class="fas fa-mail-bulk"></i></span><span class="action_btn" domain="'+val.domain+'" mail="'+val.username+'" class="manage-alias action_btn"  title="Email Alias" onclick="addmailalias(this)"><i class="fas fa-at"></i></span></td> </tr>');
                }else{
                     jQuery('.email-tbl tbody').append('<tr><td>'+val.name+'</td><td style="word-break: break-all;">'+val.username+'</td><td>('+val.used+'/'+val.quota+')</td><td><span class="action_btn mail-set" title="Change password"  domain="'+val.domain+'" mail="'+val.username+'"onclick="mailsetting(this)" ><i class="fas fa-cogs"></i></span><span class="action_btn" title="Enable/Disable" domain="'+val.domain+'"  status="'+emailstatus+'" mail="'+val.username+'" style="color:'+statusclass+';" onclick="emailstatus(this)"><i class="fas fa-power-off"></i></span><span class="action_btn" title="Delete" domain="'+val.domain+'" mail="'+val.username+'" style="color:#dc3545" onclick="delEmail(this)"><i class="fas fa-trash-alt"></i></span><span class="action_btn" title="Forward Email" domain="'+val.domain+'" mail="'+val.username+'" onclick="forwordEmail(this)"><i class="fas fa-share-square"></i></span><span class="action_btn" title="Email POP" domain="'+val.domain+'" mail="'+val.username+'"" imapstatus="'+imapStatus+'" popstatus="'+popStatus+'" style="color:'+statusclasspop+'" onclick="popEmail(this)"><i class="fas fa-envelope-open-text"></i></span><span class="action_btn" title="Email IMAP" domain="'+val.domain+'" mail="'+val.username+'" imapstatus="'+imapStatus+'" popstatus="'+popStatus+'" style="color:'+statusclassimap+'" onclick="emailImap(this)"><i class="fas fa-mail-bulk"></i></span><span class="manage-alias action_btn" domain="'+val.domain+'" mail="'+val.username+'" title="Email Alias"   onclick="addmailalias(this)"><i class="fas fa-at"></i></span><span class="action_btn" title="Upgrade Quota" domain="'+val.domain+'" mail="'+val.username+'" onclick="upgradequota(this);return false;"><i class="fa fa-level-up" aria-hidden="true"></i></span></td> </tr>');
            // $('.email-tbl tr:last').after('<tr><td>'+val.name+'</td><td>'+val.username+'</td><td>'+val.domain+'</td><td>'+val.emails+'</td><td>('+val.used+'/'+val.quota+')</td><td>a</td></tr>');
                }
               
            });
        }
    });
}

function Addmail(that,pw, cpw){
    jQuery('error-email').html('');
    jQuery('.cfameerr, .passworderr').remove();
    if (!jQuery('#' + pw).val()) {
        jQuery('#' + pw).focus().after('<p class="passworderr">Password is required!</p>');
        return false;
    } else if (!jQuery('#' + pw).val().match(/^(?=.*?[a-z])(?=.*?[0-9]).{8,}$/)) {
        jQuery('#' + pw).focus().after('<p class="passworderr">Password is weak!, &nbsp; Password must be at least 8 characters in length and include 1 digit. For example: test1234</p>');
        return false;
    } else if (!jQuery('#' + cpw).val()) {
        jQuery('#' + cpw).focus().after('<p class="passworderr">Confirm Password is required!</p>');
        return false;
    } else if (jQuery('#' + pw).val() != jQuery('#' + cpw).val()) {
        jQuery('#' + cpw).focus().after('<p class="cfameerr">Passwords must match!</p>');
        return false;
    } else {
        var formData = jQuery('#email-form').serialize();
        jQuery(that).html('<span style="margin-right:5px"><i class="fas fa-spinner fa-pulse"></i></span>Loading...');
        jQuery(that).prop('disabled', true);
        jQuery.ajax({
            type: 'post',
            url: '',
            data: 'ajaxcall=true&activity=addemail&'+formData,
            success: function (response) {
               
                jQuery(that).html('Add Email');
                jQuery(that).prop('disabled', false);
                //jQuery('#addemailmodal').modal('hide'); 
                document.getElementById('mailmodalclose').click();
                if(response == 'success'){
                    jQuery('#error-email').html('<div class="alert alert-success" role="alert">Email created successfully!</div>');
                    setTimeout(function(){
                       jQuery('#error-email').html('');
                       getEmailList();
                    }, 3000);
                }else{
                    jQuery('#error-email').html('<div class="alert alert-danger" role="alert"> '+response+'</div>');
                    setTimeout(function(){
                       jQuery('#error-email').html('');
                    }, 5000);
                }
                
            }
        });
    }
}


function domainadd(that){
    jQuery('#error-d').html('');
   
    var domainName = jQuery('#d-name').val();
    var pattern = new RegExp(/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/igm);
    
    var checkdomain = pattern.test(domainName);
    
    if(checkdomain===true) {
        
        jQuery(that).html('<span style="margin-right:5px"><i class="fas fa-spinner fa-pulse"></i></span>Loading...');
        jQuery(that).prop('disabled', true);
        jQuery.ajax({
            type: 'post',
            url: '',
            data: {ajaxcall : true,activity:'domainadd','domain':domainName},
            success: function (response) {
                
                jQuery(that).html('Add');
                //jQuery(that).prop('disabled', false);
               // jQuery('#adddomainmodal').modal('hide');
                if(response == 'success'){
                    jQuery('#error-d').html('<span style="color:#155724;">Domain added successfully!</span>');
                    //jQuery('#adddomainmodal').modal('hide');
                    
                    setTimeout(function(){
                        jQuery('#error-d').fadeOut(1000);
                    /*    $('#adddomainmodal').find('.close').trigger('click');$('#adddomainmodal').find('.close').trigger('click');*/
                       document.getElementById('add_domain_closebtn').click(); 
                        getdomainlist(); 
                     }, 3000);
                }else{
                    jQuery('#error-d').html('<span style="color:#dc3545">'+response+'</span>');
                }
            }
         });
    }else{
       jQuery('#error-d').html('<span style="color:#dc3545">Please enter valid domain!</span>');
    }

   
}

function emailvalidation(email,type){
    if(type == 'mail'){
        var testEmail = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
    }else{
        var testEmail = /^\b[A-Z0-9._%-]+[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
    }

    if (testEmail.test(email)){
        return 'valid';
    }else{
        return 'notvalid';
    }
}

function deldomain(that,dname){
   
    jQuery.confirm({
        title: 'Confirm!',
        content: 'Are you sure to delete this domain?',
        buttons: {
            confirm: function () {

                jQuery.confirm({
                title: 'Confirm!',
                content: 'Are you sure to delete all emails accounts with this domain?',
                buttons: {
                    confirm: function () {

                      jQuery('#error-del').html('<span style="font-size:24px;color:#bd2130"><i class="fas fa-spinner fa-pulse"></i></span>');
                          jQuery.ajax({
                            type: 'post',
                            url: '',
                            data: {ajaxcall : true,activity:'domaindelete','domain':dname},
                            success: function (response) {
                                 
                                //jQuery(that).html('Add');
                                //  jQuery(that).prop('disabled', false);
                                if(response == 'success'){
                                    jQuery('#error-del').html('<div class="alert alert-success" role="alert">Domain deleted successfully!</div>');
                                    getdomainlist();

                                   setTimeout(function(){ jQuery('#error-del').fadeOut(1000);}, 3000);          

                                }else{
                                    jQuery('#error-del').html('<div class="alert alert-danger" role="alert">'+response+'</div>');
                                }
                                //   jQuery('#error-del').fadeIn('slow').delay(5000).hide(0);
                            }
                         });
                    },
                    cancel: function () {
                       // jQuery.alert('Canceled!');
                    },
                }
            });
            },
            cancel: function () {
               // jQuery.alert('Canceled!');
            },
        }
    });
}

function passwordConfrm(obj, pw, cpw) {
    jQuery('.cfameerr, .passworderr').remove();
     
    var psswdtext = 'Password is required!';
    if (jQuery(obj).attr('id') == 'change_mailcfpassword' || jQuery(obj).attr('id') == 'mailcfpassword')
        psswdtext = 'Confrim Password is required!';
    if (jQuery(obj).attr('id') == 'change_mailpassword' || jQuery(obj).attr('id') == 'mailpassword') {
        if (!jQuery(obj).val()) {
            jQuery(".btn_addemail").prop("disabled", true);
            jQuery(obj).after('<p class="passworderr">' + psswdtext + '</p>');
            return false;
        }
        if (!jQuery(obj).val().match(/^(?=.*?[a-z])(?=.*?[0-9]).{8,}$/)) {
            jQuery(".btn_addemail").prop("disabled", true);
            jQuery(obj).after('<p class="passworderr">Password is weak!, &nbsp; Password must be at least 8 characters in length and include 1 digit. For example: test1234</p>');
            return false;
        } else
            jQuery('.passworderr').remove();
            jQuery(".btn_addemail").prop("disabled", false);
    }
    if (jQuery('#' + pw).val() != jQuery('#' + cpw).val()) {
        jQuery(".btn_addemail").prop("disabled", true);
        jQuery('#' + cpw).after('<p class="cfameerr">Passwords must match!</p>');
        return false;
    } else
        jQuery('.cfameerr').remove();
        jQuery(".btn_addemail").prop("disabled", false);
}

function submitMailForm(obj, pw, cpw) {
    jQuery('.cfameerr, .passworderr').remove();
    if (!jQuery('#' + pw).val()) {
        jQuery('#' + pw).focus().after('<p class="passworderr">Password is required!</p>');
        return false;
    } else if (!jQuery('#' + pw).val().match(/^(?=.*?[a-z])(?=.*?[0-9]).{8,}$/)) {
        jQuery('#' + pw).focus().after('<p class="passworderr">Password is weak!, &nbsp; Password must be at least 8 characters in length and include 1 digit. For example: test1234</p>');
        return false;
    } else if (!jQuery('#' + cpw).val()) {
        jQuery('#' + cpw).focus().after('<p class="passworderr">Confirm Password is required!</p>');
        return false;
    } else if (jQuery('#' + pw).val() != jQuery('#' + cpw).val()) {
        jQuery('#' + cpw).focus().after('<p class="cfameerr">Passwords must match!</p>');
        return false;
    } else {
        jQuery(obj).attr('type', 'submit').trigger('click');
    }
}

function slidediv(classname,classname2,classname3) {
    jQuery("." + classname).toggle("slow");
    jQuery("." + classname2).slideUp("slow");
    //jQuery("." + classname3).slideUp("slow");
  
    let searchParams = new URLSearchParams(window.location.search);
    var check = searchParams.has('page');
    
    if(check == true){
        var url = jQuery(location).attr('href');
        var currenturl = url.substr(0,url.lastIndexOf('&'));
        currenturl = currenturl+'&page='+classname;
    }else{
        var currenturl = jQuery(location).attr('href')+'&page='+classname;
    }
    
    window.history.pushState({path:currenturl},'',currenturl);

    //  $("." + classname3).slideUp("slow");
    //$("." + classname4).slideUp("slow");
}
/*     function slideclosediv(classname, classname2, classname3, classname4) {
    $("." + classname).toggle("slow");
    $("." + classname2).slideUp("slow");
    $("." + classname3).slideUp("slow");
    $("." + classname4).slideUp("slow");
}
*/
function randomString(randomField) {
    var chars = "ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz!@#$^&()";
    var string_length = 10;
    var randomstring = '';
    var charCount = 0;
    var numCount = 0;

    for (var i = 0; i < string_length; i++) {
        // If random bit is 0, there are less than 3 digits already saved, and there are not already 5 characters saved, generate a numeric value.
        if ((Math.floor(Math.random() * 2) == 0) && numCount < 3 || charCount >= 5) {
            var rnum = Math.floor(Math.random() * 10);
            randomstring += rnum;
            numCount += 1;
        } else {
            // If any of the above criteria fail, go ahead and generate an alpha character from the chars string
            var rnum = Math.floor(Math.random() * chars.length);
            randomstring += chars.substring(rnum, rnum + 1);
            charCount += 1;
        }
    }

    jQuery("#" + randomField).val(randomstring);
}

function submitForm(fname) {
    jQuery("#" + fname).submit();
}

jQuery(document).ready(function () {

    jQuery('.iredmail').parent().css('border', 'none');
    // Setting for forward email
    jQuery(".forwardemail").click(function () {
        var account = jQuery(this).attr('accesskey');
        jQuery("#mail_account").val(account);
        jQuery("#mail_account_forward_span").html(account);
        // var allforwardemails = {/literal}{$forwardemaillist}{literal};
        // $("#previous_forward_email").val(allforwardemails[account]);
        // $("#new_forward_email").val(allforwardemails[account]);
    });
    // Setting for changepassword
    jQuery(".changepassword").click(function () {
        var account = jQuery(this).attr('accesskey');
        jQuery("#mail_account_password_span").html(account);
        jQuery("#mail_account_name").val(account);
    });
});