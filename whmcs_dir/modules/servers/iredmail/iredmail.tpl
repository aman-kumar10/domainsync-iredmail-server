<script type="text/javascript" src="{$url}assets/js/miniPopup.js?v=122"></script>
<script type="text/javascript" src="{$url}assets/js/script.js?v=122"></script>
<link rel="stylesheet" type="text/css" href="{$url}assets/css/miniPopup.css?v=122" />
<link rel="stylesheet" type="text/css" href="{$url}assets/css/style.css?v=122" />
<link rel="stylesheet" type="text/css" href="{$url}assets/css/tooltip.css?v=122" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css?v=122">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js?v=122"></script>

{literal}
    <script>
    var defaultQuotaPrice = '{/literal}{$upgrade_price["price"]}{literal}';
    var defaultPlanType = '{/literal}{$planselected}{literal}';
    </script>
{/literal}
  
<div class="container-fluid iredmail custom-{$template}">
    {if $error}
        <div class="alert alert-danger">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <strong>{$lang['err']}</strong> {$error}
        </div>
    {/if}
    {if $result}
        <div class="alert alert-success">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <strong>{$lang['congo']}</strong> {$result}
        </div>
    {/if}
    <div class="row" style="text-align:left; padding-top: 10px;">
        <div class="col-md-12">

            <button type="button" class="btn btn-default btn-md" onclick="slidediv('managedomain','manageemail','manageemailalias');"><span ><i class="fas fa-tasks"></i></span>&nbsp;{$lang['ManageDomains']}</button>

            <button type="button" class="btn btn-default btn-md" onclick="slidediv('manageemail','managedomain','manageemailalias');"><span ><i class="fas fa-envelope-open-text"></i></span>&nbsp;{$lang['ManageEmailsAccount']}</button>

           <!--  <button type="button" class="btn btn-default btn-md" onclick="slidediv('manageemailalias','manageemail','managedomain');" ><span ><i class="fas fa-envelope-open-text"></i></span>&nbsp;{$lang['ManageEmailsAlias']}</button>
            -->
            <button type="submit" class="btn btn-default btn-md" onclick="window.open('{$webmail}')"><span  ><i class="fas fa-tasks"></i></span>&nbsp;{$lang['web_mail']}</button>
            {if $is_admin == 'on'}
                <button type="button" class="btn btn-default btn-md" onclick="window.open('{$web_admin}')"><span><i class="fas fa-user-shield"></i></span>&nbsp;{$lang['web_admin']}</button>
            {/if}
        </div>
    </div>

    <hr/>

    <div class="managedomain" >
        <div class="domain-inner-sec">
            <div class="title-ired">  
                <h3>{$lang['domain_managelist_title']}</h3> 
                <div  id="btn-domain-div">
                     
                  <button type="button" id="btn-domain" class="btn btn-primary" data-toggle="modal" data-target="#adddomainmodal">
                    {$lang['domain_add_btn']}
                  </button>
                </div>
            </div>
           <div id="error-del"></div>
            <table class="table domain-tbl" id="domain_list">
              <thead class="thead-dark">
                <tr>
                  <th scope="col">{$lang['domain_list_domain']}</th>
                  <th scope="col"> {$lang['domain_list_emailaccounts']}</th>
                  <th scope="col">{$lang['mailalias_action']}</th>
                </tr>
              </thead>
              <tbody>
                {foreach from=$domainlist  key=k item=list} 
                <tr>
                  <td scope="row">{$k} {if $list['domainalias']|@count gt 0}<div id="alias_list" style="display: block;">{$list['domainalias'][0]['target_domain']}<span style="margin-left: 10px;color:#d9534f;cursor: pointer;" alias="{$list['domainalias'][0]['target_domain']}"  title="Delete Alias" domain="{$k}"onclick="deldomainalias(this)"><i class="fas fa-trash-alt"></i></span></div>{/if}</td>
                  <td>{$list['emailcount']}</td>
                  <td><span  domain="{$k}" title="Manage Catch-All"class="manage-catchall" onclick="catchAll(this,'{$k}')"><i class="fas fa-envelope-open-text"></i></span><span  title="Delete" class="manage-d" onclick="deldomain(this,'{$k}')"><i class="fas fa-trash-alt"></i></span><span  title="Domain Alias" class="manage-alias" onclick="addalias(this,'{$k}')"><i class="fas fa-at"></i></span></td>
                </tr>
                {/foreach}
                
              </tbody>
            </table>
        </div>
    </div>
    <div class="manageemail" style="display:none">
         <div class="domain-inner-sec">
            <div class="title-ired">  
                <h3>{$lang['maillist_manage_title']}</h3>
                <div  id="btn-email-div">
                    <button type="button" id="btn-email-ac" class="btn btn-primary btn_modalreset" data-toggle="modal" data-target="#addemailmodal">
                        {$lang['maillist_add_mail_btn']}
                    </button>
                </div>
            </div>  
                 
            <div id="error-email"></div>
            <table class="table email-tbl">
              <thead class="thead-dark">
                <tr>
                  <th scope="col"width="15%">{$lang['maillist_name']}</th>
                  <th scope="col" width="20%">{$lang['maillist_mail']}</th>
                <!--   <th scope="col" width="20%">Domain Name</th> -->
                  <!--  <th scope="col">Sent Emails</th> -->
                  <th scope="col" width="10%">{$lang['maillist_quota']}</th>
                  <th scope="col" width="25%">{$lang['maillist_action']}</th>
                </tr>
              </thead>
              <tbody>
                
              </tbody>
            </table>
        </div>
    </div>
     <div class="manageemailalias" style="display:none">
         <div class="domain-inner-sec">
            <div class="title-ired">  
                <h3>{$lang['mailalias_title']}</h3>
                <div  id="btn-email-div">
                    <button type="button" id="btn-email-ac" class="btn btn-primary btn_modalreset" data-toggle="modal" data-target="#emailaliasmodal">
                      {$lang['mailalias_add_btn']}
                    </button>
                </div>
            </div>  
             <div id="emailalias-msg"></div>
             <div id="emailalias-loader" style="display:none"><i class="fas fa-spinner fa-pulse"></i></div>
             <table class="table" id="alias_tbl">
            <thead class="thead-dark tbl_alias">
              <tr>
                <th scope="col">{$lang['mailalias_displayname']}</th>
                <th scope="col">{$lang['mailalias_mail']}</th>
                <th scope="col">{$lang['mailalias_domain']}</th>
                <th scope="col">{$lang['mailalias_deliver']}</th>
                <th scope="col">{$lang['mailalias_action']}</th>
              </tr>
            </thead>
            <tbody>
               
            </tbody>
          </table>
           
        </div>
    </div>

<!-- catch all  Modal -->
<div class="modal fade" id="catchallmodal" tabindex="-1" role="dialog" aria-labelledby="catchallmodalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="catchallmodalLabel">{$lang['catch_all_title']}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="catchallmodal-form">
            <div id="catchallmodal-loader" style="display:none"><i class="fas fa-spinner fa-pulse"></i></div>
            <div id="catchallmodal-d"></div>
            <input type="hidden" name="catch_domain_selected" id="catch_domain_selected"value=""/>
          <div class="form-group">
            <label for="d-name" class="col-form-label">{$lang['catch_all_addresses']} ( <span id="catch_domain"></span> )</label>
            <select id="catachall_mail" name="catchallmail"   class="form-control">
                
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="add_domain_close">Close</button>
        <button type="button" class="btn btn-primary catch_add_btn"  onclick="catchalladd(this); return false;" disabled>Add</button>
      </div>
    </div>
  </div>
</div>

<!-- domain Modal -->
<div class="modal fade" id="adddomainmodal" tabindex="-1" role="dialog" aria-labelledby="adddomainmodalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="adddomainmodalLabel">{$lang['add_domain_btn']}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="add_domain_closebtn">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="domain-form">
            <div id="domain-loader" style="display:none"><i class="fas fa-spinner fa-pulse"></i></div>
            <div id="error-d"></div>
          <div class="form-group">
            <label for="d-name" class="col-form-label">{$lang['add_domain_name']}:</label>
            <input type="text" class="form-control" id="d-name">
            <p id="d_name_error" style="display:none"></p>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" >Close</button>
        <button type="button" class="btn btn-primary domain_add_btn"  onclick="domainadd(this); return false;" disabled>Add</button>
      </div>
    </div>
  </div>
</div>

<!-- Email Forwording Modal -->
<div class="modal fade" id="emailforwordingmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{$lang['forwarding_mail']}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="emailforwording-form">
          <input type="hidden" name="mailaddress" id="mailaddress" value="" />
          <input  type="hidden" name="domain_name" id="domain_name" value="" />
            <div id="emailforwording-loader" style="display:none"><i class="fas fa-spinner fa-pulse"></i></div>
            <div id="error-emailforwording" ></div>
          <div class="form-group">
            <label for="email-forwording" class="col-form-label">{$lang['forwarding_mail_adds']}:</label>
            <textarea id="email-forwording" name="mailforwarding" rows="4" cols="50" placeholder="One mail address per line. Invalid address will be discarded.">
    
            </textarea>

          </div>
          <span class="email-forwording-note">{$lang['note_mailaddress']}</span>
            
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary domain_add_btn"  onclick="forwordingmailadd(this); return false;">Add</button>
      </div>
    </div>
  </div>
</div>

<!-- Set domain  alias mail Modal -->
<div class="modal fade" id="domainaliasmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{$lang['domain_alias']}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="domainalias-form">
          <div id="error-domainalias"></div>
          <!-- <input type="hidden" name="mailaddress_alias" id="mailaddress_alias" value="" /> -->
          <input  type="hidden" name="domain_alias_dname" class="domain_name_alias" value="" />
          <div id="doaminalias-loader" style="display:none"><i class="fas fa-spinner fa-pulse"></i></div>
          <!-- <label for="targetdomain-name" class="col-form-label">Enter Domain Alias:</label>
          <div class="form-group">
            <textarea id="targetdomain-name" name="targetdomain" rows="6" cols="65" >
            </textarea>
          </div> -->
         
         <div class="form-group">
            <label for="targetdomain-name" class="col-form-label">{$lang['domain_alias_enter']}:</label>
            <input id="targetdomain-name" name="targetdomain"  class="form-control" >
          </div>
          <p id="targetdomain_error" style="display:none"></p>
        <!--   <div class="form-group col-md-6">
          <span class="note-alias">Email address of alias account must end with domain name(s):<p id="domain_alias_dname"> testdomainman3.com.</p></span>
          </div>
          <div class="form-group">
          <span class="email-forwording-note">Note: One mail address per comma separated. Invalid address will be discarded.</span>
        </div> -->
        </form>
        <!-- <span class="email-forwording-note">Note: One domain address per comma separated. Invalid address will be discarded.</span> -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary targetdomain_add_btn"  onclick="domainaliasadd(this); return false;" disabled>Add</button>
      </div>
    </div>
  </div>
</div>

<!-- Set mail  alias new Modal -->
<div class="modal fade" id="setmailaliasmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{$lang['email_alias_addtitle']}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="setmailalias-loader" style="display:none"><i class="fas fa-spinner fa-pulse"></i></div>
        <form id="setmailalias-form">
          <div id="error-setmailalias"></div>
          <input type="hidden" name="mailaddress_alias" id="mailaddress_alias" value="" />
          <input  type="hidden" name="mail_alias_dname" id="mail_alias_dname" value="" />
          
          <label for="mailalias-name" class="col-form-label">{$lang['email_alias_addresses']}:</label>
          <div class="form-group mailalias-outer">
            <textarea id="mailalias-name" name="mailalias" rows="7" cols="35" >
            </textarea>
              <span class="note-alias">
              <p><i class="fas fa-dot-circle"></i>{$lang['email_alias_addresses_note']} <span id="mailaddress_name"> </span></p><br>
              <p><i class="fas fa-dot-circle"></i>{$lang['email_alias_addresses_note_domain']}: <span id="maildomain_name"> </span></p>
           <!--  <ul id="mail_alias">
              <li>Emails sent to alias addresses will be delivered to <p id="email_alias_address"> testdomainman3.com.</p></li>
              <li>Email address of alias account must end with domain name(s):<p id="domain_alias_dname"> testdomainman3.com.</p></li>
            </ul> -->
            <br>
          </span>
          </div>
          <div class="form-group enable_alias">
            <input type="checkbox" id="enableAlias" name="enable"
                   checked>
            <label for="enableAlias">{$lang['email_alias_tick']}</label>
          </div>
          <div class="form-group">
          <span class="email-forwording-note">{$lang['note_mailaddress']}.</span>
        </div> 
        </form>
         
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary alias_mailadd_btn"  onclick="setmailaliasadd(this); return false;" >Add</button>
      </div>
    </div>
  </div>
</div>  

<!-- upgrade email quota Modal -->
<div class="modal fade" id="upgradequotamodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{$lang['mail_quota_upgrade']} ( <span id="chnge-mail-select-quota"> </span>)</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="change-quota">
          
            <div id="message-changequota"></div>
             <input   name="mailaddress" type="hidden" value="" id="quota_chnge_mail">
             <input   name="domainname" type="hidden" value="" id="domain_quotachnge">
           
            <div id="change-pass-div">
               
                <div class="form-group">
                    <label>{$lang['mail_quota_upgrade_gb']} :</label>
                    <input type="number"  min="0" max="50" name="quotachange" class="form-control" id="quotachange" value="1">
                </div>
                 <div class="form-group">
                    <label>{$lang['mail_quota_upgrade_price']}:  {$upgrade_price['prefix']} <span id="upgrade_price">{$upgrade_price['price']}</span> {$upgrade_price['suffix']} </label>
                </div>
               
            </div>
            
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="btn_upgrade_quota" onclick="quotaupgrade(this); return false;">Upgrade</button>
      </div>
    </div>
  </div>
</div>


<!-- Edit Email alias Modal -->
<!-- <div class="modal fade" id="editmailaliasmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg custom_modal" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit Email Alias</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="mailaliasmodalclose">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      
        <div id="alias_add_tab" >
          <form id="editemailalias-form">
           <div id="error-emailaliasupdate"></div> 
           <div id="emailaliasupdate-loader" style="display:none"><i class="fas fa-spinner fa-pulse"></i></div>
           <label>Profile of alias : <span class="edit_mailaddress_head"> </span></label>
            <input type="hidden" name="mailaddress_alias" id="edit_mailaddress_alias" value="" />
            <input  type="hidden" name="domain_name_alias" id="edit_domain_name_alias" value="" />
       
            <div class="form-group">
              <label for="edit_name" class="col-form-label">Display Name</label>
              <input  type="text" name="displayname" id="edit_name" value="" class="form-control"/>
            </div>
            <div class="form-group col-md-4">
              <label for="email-name-alias" class="col-form-label">Who can send email to this list :</label>
            </div>
            <div class="col-md-8">
              <div  class="form-check radio-alias">
                <input type="radio" class="form-check-input" id="public_update" name="policy" value="public" checked>
                <label for="public" class="form-check-label" >Unrestricted. Everyone can send mail to this address</label>
              </div>
              <div class="form-check radio-alias">
                <input type="radio" class="form-check-input" id="domainonly_update" name="policy" value="domain" >
                <label for="domainonly_update" class="form-check-label" >Users under same domain</label>
              </div>
              <div class="form-check radio-alias">
                <input type="radio" class="form-check-input" id="subdomain_update" name="policy" value="subdomain" >
                <label for="subdomain_update" class="form-check-label" >Users under same domain and its sub-domains</label>
              </div>
            
              <div class="form-check radio-alias">
                <input type="radio" class="form-check-input" id="members_update" name="policy" value="membersonly" >
                <label for="members_update" class="form-check-label" >Members</label>
              </div>
              
              <div class="form-check radio-alias">
                <input type="radio" class="form-check-input" id="moderators_update" name="policy" value="moderatorsonly" >
                <label for="moderators_update" class="form-check-label" >Moderators</label>
              </div>
           
              <div class="form-check radio-alias">
                <input type="radio" class="form-check-input" id="membersm_update" name="policy" value="membersandmoderatorsonly" >
                <label for="membersm_update" class="form-check-label" >Members and moderators</label>
              </div>
            
            </div>
            
          </form>
        </div>
         
      </div>
      <div class="modal-footer alias_add_tab" >
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary alias_update_btn"  onclick="emailaliasupdate(this); return false;" >Update</button>
      </div>
    </div>
  </div>
</div> -->
<!-- Email alias Modal -->
<!-- <div class="modal fade" id="emailaliasmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg custom_modal" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Email Alias</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="emailaliasmodalclose">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      
        <div id="alias_add_tab" >
          <form id="emailalias-form">
            <div id="error-emailalias"></div>
          
            <div id="emailaliasadd-loader" style="display:none"><i class="fas fa-spinner fa-pulse"></i></div>
            <div class="form-group">
              <label   class="col-form-label">Add mail user under domain <span id="req">*</span></label>
              <select id="existdomainalias" name="domain_name_alias" onchange="selectdomainAlias(this);" class="form-control">

              {if $domainlist|@count > 0 }
                    {foreach from=$domainlist  key=k item=list}
                   <option value="{$k }">{$k}</option>
                   
                 {/foreach}
              {else}
                <option value="">Please add domain first! </option>
              {/if}
             </select>
            </div>
             <label for="inputEmail3" class="col-form-label form-group">Mail Address*</label> 
            <div class="input-group mb-3">
                <input   type="text" name="mail_address" id="email-name-alias"  class="form-control valid_domain"   required pattern="[a-zA-Z0-9_.-]*"  >
              <div class="input-group-append">
                <span class="input-group-text selected-dname"></span>
              </div>
              <p id="valid-msg-alias"></p>
              
            </div>
            <div class="form-group">
              <label for="email-name-alias" class="col-form-label">Display Name</label>
              <input  type="text" name="displayname" id="display-name" value="" class="form-control"/>
            </div>
            <div class="form-group col-md-4">
              <label for="email-name-alias" class="col-form-label">Who can send email to this list :</label>
            </div>
            <div class="col-md-8">
              <div  class="form-check radio-alias">
                <input type="radio" class="form-check-input" id="public" name="policy" value="public" checked>
                <label for="public" class="form-check-label" >Unrestricted. Everyone can send mail to this address</label>
              </div>
              <div class="form-check radio-alias">
                <input type="radio" class="form-check-input" id="domainonly" name="policy" value="domain" >
                <label for="domainonly" class="form-check-label" >Users under same domain</label>
              </div>
              <div class="form-check radio-alias">
                <input type="radio" class="form-check-input" id="subdomain" name="policy" value="subdomain" >
                <label for="subdomain" class="form-check-label" >Users under same domain and its sub-domains</label>
              </div>
            
              <div class="form-check radio-alias">
                <input type="radio" class="form-check-input" id="members" name="policy" value="membersonly" >
                <label for="members" class="form-check-label" >Members</label>
              </div>
              
              <div class="form-check radio-alias">
                <input type="radio" class="form-check-input" id="moderators" name="policy" value="moderatorsonly" >
                <label for="moderators" class="form-check-label" >Moderators</label>
              </div>
           
              <div class="form-check radio-alias">
                <input type="radio" class="form-check-input" id="membersm" name="policy" value="membersandmoderatorsonly" >
                <label for="membersm" class="form-check-label" >Members and moderators</label>
              </div>
            
            </div>
            
          </form>
        </div>
         
      </div>
      <div class="modal-footer alias_add_tab" >
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary alias_add_btn"  onclick="emailaliasadd(this); return false;" >Add</button>
      </div>
    </div>
  </div>
</div> -->

<!-- manage email Modal -->
<div class="modal fade" id="manageemailmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{$lang['mail_changepassword']}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="change-password">
            <div id="domain-loader" style="display:none"><i class="fas fa-spinner fa-pulse"></i></div>
            <div id="error-change"></div>
             <input   name="mailaddress" type="hidden" value="" id="mailaddress_chnge">
             <input   name="domainname" type="hidden" value="" id="domain_chnge">
          
            <div id="change-pass-div">
                <div > <b>{$lang['mail_changepasswordmail']} : <span id="chnge-mail-select"> </span></b>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" id="change_mailpassword" placeholder="{$lang['password']}" required  title="Password must contain at least eight characters. We suggested you to use strong password." onkeyup="passwordConfrm(this, 'change_mailpassword', 'change_mailcfpassword')">
                </div>
                <div class="form-group">
                    <input type="password" name="confirmpassword" class="form-control" id="change_mailcfpassword" placeholder="{$lang['c_pass']}" title="Please enter the same Password as above" required  onkeyup="passwordConfrm(this, 'change_mailpassword', 'change_mailcfpassword')">
                </div>
            </div>
            
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="updatedata(this,'change_mailpassword', 'change_mailcfpassword'); return false;">Update</button>
      </div>
    </div>
  </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="addemailmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{$lang['mail_addmodal_title']}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="mailmodalclose">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="email-form">
            <div id="domain-loader" style="display:none"><i class="fas fa-spinner fa-pulse"></i></div>
            <div id="error-d"></div>
            <div class="form-group">
            <label   class="col-form-label">{$lang['mail_addmodal_user']} <span id="req">*</span></label>
            <select id="existdomain" name="domain" onchange="selectdomain(this);" class="form-control">

            {if $domainlist|@count > 0 }
                  {foreach from=$domainlist  key=k item=list}
                 <option value="{$k }">{$k}</option>
                 
               {/foreach}
            {else}
              <option value="">{$lang['mail_addmodal_domain_msg']} </option>
            {/if}
             </select>
            </div>
            <label for="inputEmail3" class="col-form-label form-group">{$lang['mail_addmodal_address']} <span id="req">*</span></label>  
            <div class="input-group mb-3">
                <input   type="text" name="mail" class="form-control valid_domain" id="inputEmail3" placeholder="{$lang['mail_address']}" required pattern="[a-zA-Z0-9_.-]*" title="Username must contain only letters, numbers and underscores!">
            <div class="input-group-append">
              <span class="input-group-text select-dname"> </span>
            </div>
            
          </div>
          <p id="valid-msg" style="display:none"></p>
          <div class="form-group">
            <label for="mailpassword" class="col-form-label">{$lang['mail_addmodal_pass']} <span id="req">*</span></label>
            <input type="password" name="password" class="form-control" id="mailpassword" placeholder="{$lang['password']}" required  title="Password must contain at least eight characters. We suggested you to use strong password." onkeyup="passwordConfrm(this, 'mailpassword', 'mailcfpassword')">

          </div>
          <div class="form-group">
            <label for="mailcfpassword" class="col-form-label">{$lang['mail_addmodal_cfpass']} <span id="req">*</span></label>
            <input type="password" name="confirmpassword" class="form-control" id="mailcfpassword" placeholder="{$lang['c_pass']}" title="Please enter the same Password as above" required  onkeyup="passwordConfrm(this, 'mailpassword', 'mailcfpassword')">
          </div> 
          <div class="form-group">
            <label for="displayName3" class="col-form-label">{$lang['mail_addmodal_displayname']}:</label>
            <input type="text" name="displayname" required class="form-control" id="displayName3" placeholder="{$lang['display_name']}">
          </div>
        {if $planselected eq 'Business'}
            <div class="form-group">
                <label for="mailboxQuota3" class="col-form-label">{$lang['mail_class_service']}: <span id="req">*</span></label>
                <select id="servicetype" name="servicetype" class="form-control">
                    {if $emailaccountslimit|@count > 0 }
                        {foreach from=$emailaccountslimit key=k item=emailval}
                            <option value="{$emailval}_{$k}">{$k} ( {$emailval} left) </option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        {else}
            <div class="form-group">
                <label for="mailboxQuota3" class="col-form-label">{$lang['mail_quota_mb']} : <span id="req">*</span></label>
                 <input type="number"  min="{$min_quota_size}" max="{$max_quota_size}" name="quotasize" class="form-control" id="quotachange_unlimited" value="{$min_quota_size}">
                <p id="qota_size_msg">{$lang['mail_max_quota_mb']} : {$max_quota_size} MB</p> 
            </div>
        {/if}    
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn_addemail" onclick="Addmail(this,'mailpassword', 'mailcfpassword'); return false;" disabled>Add Email</button>
      </div>
    </div>
  </div>
</div>
 
</div>

 