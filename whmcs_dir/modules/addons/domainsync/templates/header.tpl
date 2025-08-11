<link rel="stylesheet" href="../modules/addons/domainsync/assets/css/admin.css">
<script src="../modules/addons/domainsync/assets/js/admin.js"></script>

<script src='{$tplVar['rootURL']}/assets/js/AdminClientDropdown.js'></script>
<script type="text/javascript">
    function getClientSearchPostUrl() { return '/{$tplVar['adminpath']}/index.php?rp=/admin/search/client'; }
</script>

<div class="add_hdr">

    <div class="add_nav">
        <ul>
            <li class="header-tab"><a href="addonmodules.php?module=domainsync" class="ad_home {if $tplVar['tab'] =='dashboard'}active {/if} "><i class="fa fa-user" aria-hidden="true"></i> {$LANG['tab_dashboard']}</a></li>
        </ul>    
    </div>

</div>