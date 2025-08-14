$(document).ready(function () {
    let domainCheckInProgress = false;
    let domainValid = false; // flag to store domain validity

    // Product change AJAX
    $("#selectedProduct").on("change", function () {
        var selectedValue = $(this).val();
        $("#cstmFldHead").show();

        $.ajax({
            method: "POST",
            data: {
                data_action: "getCustomfields",
                product_id: selectedValue
            },
            dataType: "json",
            beforeSend: function () {
                $(".customfields-loader").html('<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>').show();
                $("#customFieldsContainer").empty();
            },
            success: function (response) {
                if (response.success) {
                    $("#customFieldsContainer").html(response.html);
                } else {
                    $("#customFieldsContainer").html('<p class="text-danger">No custom fields found.</p>');
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            },
            complete: function () {
                $(".customfields-loader").hide();
            }
        });
    });

    // Domain availability check on blur
    $(document).on("blur", "input[data-text_id='domain']", function () {

        if (!$("#validateDomainID").is(":checked")) {
            domainValid = true;
            return; 
        }
        var $input = $(this);
        var domainname = $.trim($input.val());

        // Remove old messages/icons
        $input.closest(".input-with-icon").siblings(".success-message, .error-message").remove();
        $input.siblings(".input-check-icon").remove();
        domainValid = false;


        // Check domain format (example.com only)
        var domainRegex = /^(?!-)[A-Za-z0-9-]{1,63}(?<!-)\.[A-Za-z0-9]{2,}$/;
        if (!domainRegex.test(domainname)) {
            $input.closest(".input-with-icon").after('<div class="error-message">Enter a domain with a valid extension (example.com).</div>');
            return;
        }

        if (!$input.parent().hasClass("input-with-icon")) {
            $input.wrap('<div class="input-with-icon" style="position: relative; display: inline-block; width: 100%;"></div>');
        }

        domainCheckInProgress = true;

        $.ajax({
            method: "GET",
            url: "../modules/addons/domainsync/lib/Ajax.php",
            data: {
                data_action: "checkAvlDomain",
                domain_value: domainname,
            },
            dataType: "json",
            success: function (response) {
                $input.closest(".input-with-icon").siblings(".success-message, .error-message").remove();
                $input.siblings(".input-check-icon").remove();

                if (response.success === 'success') {
                    $input.closest(".input-with-icon").after('<div class="success-message">' + response.message + '</div>');
                    $input.css("padding-right", "30px");
                    $input.after('<i class="fa fa-check-circle input-check-icon" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); color: green; font-size: 18px; pointer-events: none;"></i>');
                    domainValid = true;
                } else {
                    $input.closest(".input-with-icon").after('<div class="error-message">' + response.message + '</div>');
                    domainValid = false;
                }
            },
            error: function () {
                $input.closest(".input-with-icon").after('<div class="error-message">An error occurred. Please try again.</div>');
                domainValid = false;
            },
            complete: function () {
                domainCheckInProgress = false;
            }
        });
    });

    // Form validations
    $('#btnAddOrder').on('click', function (e) {
        e.preventDefault();
        let isValid = true;

        $('.required-field').each(function () {
            let field = $(this);
            if (!field.is("[data-text_id='domain']")) { 
                field.siblings(".error-message").remove();
            }
        });

        $('.required-field').each(function () {
            let field = $(this);
            if ($.trim(field.val()) === '') {
                if (field.siblings(".error-message").length === 0) {
                    field.after('<div class="error-message" style="color:red;font-size:12px;">This field required</div>');
                }
                isValid = false;
            }
        });

        if (domainCheckInProgress) {
            alert("Please wait until the domain check is complete.");
            isValid = false;
        }

        if (!domainValid) {
            isValid = false;
        }

        $('.form-group').each(function () {
            if ($(this).find('.error-message').length > 0) {
                isValid = false;
            }
        });

        if (isValid) {
            $('.domain-submit-form').submit();
        }
    });
});
