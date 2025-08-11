$(document).ready(function() {
    $("#selectedProduct").on("change", function() {
        var selectedValue = $(this).val();
        $("#cstmFldHead").show();

        $.ajax({
            method: "POST",
            data: {
                data_action: "getCustomfields",
                product_id: selectedValue
            },
            dataType: "json",
            beforeSend: function() {
                $(".customfields-loader").html('<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>').show();
                $("#customFieldsContainer").empty();
            },
            success: function(response) {
                if (response.success) {
                    $("#customFieldsContainer").html(response.html);
                } else {
                    $("#customFieldsContainer").html(
                        '<p class="text-danger">No custom fields found.</p>'
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
            },
            complete: function() {
                $(".customfields-loader").hide();
            }
        });
    });


    // Form validations
    $("#btnAddOrder").on("click", function (e) {
        e.preventDefault(); 

        var isValid = true;

        $(".error-message").remove();
        $(".field-error").removeClass("field-error");

        $(".form-horizontal").find(".required-field").each(function () {
            var value = $.trim($(this).val());

            if (value === "" && $(this).is(":visible")) {
                isValid = false;
                $(this).addClass("field-error");
                $(this).after('<div class="error-message">This field is required.</div>');
            }
        });

        if (isValid) {
            $(".form-horizontal").submit();

            // var userID = $("#selectUserid").val();
            // var productId = $("#selectedProduct").val();
            
        }
    });

    $(".form-horizontal").on("input change", ".required-field", function () {
        if ($.trim($(this).val()) !== "") {
            $(this).removeClass("field-error");
            $(this).siblings(".error-message").remove();
        }
    });


    (function () { 
        var typingTimer; 
        var doneTypingInterval = 500; 

        $(document).on("input change", "input[name^='customfield'][data-text_id='domain']", function () {
            var $input = $(this);
            var domain = $.trim($input.val()); 

            $input.next(".success-message, .error-message").remove();

            clearTimeout(typingTimer);

            if (domain.length > 0) {
                typingTimer = setTimeout(function () {
                    $input.next(".success-message, .error-message").remove();

                    $.ajax({
                        method: "GET",
                        url: "../modules/addons/domainsync/lib/Ajax.php",
                        data: {
                            data_action: "checkAvlDomain",
                            domain_value: domain,
                        },
                        dataType: "json",
                        success: function (response) {
                            $input.next(".success-message, .error-message").remove();
                            if (response.success === 'success') {
                                $input.after('<div class="success-message">' + response.message + '</div>');
                            } else {
                                $input.after('<div class="error-message">' + response.message + '</div>');
                            }
                        },
                        error: function (xhr, status, error) {
                            $input.next(".success-message, .error-message").remove();
                            console.error("AJAX Error:", error);
                            $input.after('<div class="error-message">An error occurred. Please try again.</div>');
                        }
                    });
                }, doneTypingInterval);
            }
        });
    })();






});
