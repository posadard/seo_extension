<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<?php echo $summary_form; ?>

<?php echo $product_tabs; ?>

<div id="content" class="panel panel-default">

    <div class="panel-heading col-xs-12">
        <div class="primary_content_actions pull-left">
            <div class="btn-group mr10 toolbar">
                <?php echo $this->getHookVar('common_content_buttons'); ?>

                <button type="button" id="test_ai_connection" class="btn btn-info tooltips" 
                        title="<?php echo $button_test_ai_connection; ?>">
                    <i class="fa fa-flask fa-lg"></i> <?php echo $button_test_ai_connection; ?>
                </button>

                <button type="button" id="preview_schema" class="btn btn-success tooltips" 
                        title="<?php echo $button_preview_schema; ?>">
                    <i class="fa fa-eye fa-lg"></i> <?php echo $button_preview_schema; ?>
                </button>

                <button type="button" id="generate_others_content" class="btn btn-primary tooltips" 
                        title="Auto-generate shipping & return policy defaults">
                    <i class="fa fa-cogs fa-lg"></i> Auto-Generate Defaults
                </button>

                <?php echo $this->getHookVar('extension_toolbar_buttons'); ?>
            </div>
        </div>

        <?php include($tpl_common_dir . 'content_buttons.tpl'); ?>
    </div>

    <?php echo $form['form_open']; ?>
    <div class="panel-body panel-body-nopadding tab-content col-xs-12">

        <div class="alert alert-info">
            <div class="row">
                <div class="col-md-8">
                    <strong><i class="fa fa-info-circle"></i> Individual AI Generation:</strong><br>
                    <span>Each content section has its own AI generation button. Configure min/max tokens in extension settings to control content length.</span>
                </div>
                <div class="col-md-4 text-right">
                    <strong>Token Settings:</strong><br>
                    <span class="label label-info" id="max_tokens_display">Max Tokens: Loading...</span><br>
                    <span class="label label-warning" id="min_tokens_display" style="margin-top: 3px;">Min Tokens: Loading...</span>
                </div>
            </div>
        </div>

        <div id="ai_status_alert" class="alert alert-info" style="display: none;">
            <i class="fa fa-info-circle fa-fw fa-lg"></i>
            <span id="ai_status_message"></span>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-cog"></i> <?php echo $text_section_basic; ?>
                </h4>
            </div>
            <div class="panel-body">
                
                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="custom_description">
                        <?php echo $entry_custom_description; ?>
                    </label>
                    <div class="col-sm-6 col-xs-12">
                        <textarea 
                            id="custom_description" 
                            name="custom_description" 
                            class="form-control large-field" 
                            rows="4" 
                            placeholder="Product description for Schema.org markup..."><?php echo $schema_settings['custom_description'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <button type="button" id="generate_description_ai" class="btn btn-success btn-block" onclick="generateDescriptionAI()">
                            <i class="fa fa-magic"></i> Generate Description
                        </button>
                        <div class="token-info-box" style="margin-top: 8px;">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Uses individual token limits<br>
                                <i class="fa fa-clock-o"></i> Min-Max token range applied
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="enable_variants">
                        <?php echo $entry_enable_variants; ?>
                    </label>
                    <div class="input-group afield col-sm-6 col-xs-12">
                        <?php echo $form['fields']['enable_variants']; ?>
                    </div>
                </div>

                <div id="variants_preview" class="form-group" style="display: none;">
                    <label class="control-label col-sm-3 col-xs-12">Product Variants Found:</label>
                    <div class="col-sm-6 col-xs-12">
                        <div id="variants_list" class="well well-sm">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-magic"></i> <?php echo $text_section_ai; ?> 
                    <small class="text-muted">- Independent Generation</small>
                </h4>
            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="faq_content">
                        <?php echo $entry_faq_content; ?>
                    </label>
                    <div class="col-sm-6 col-xs-12">
                        <textarea 
                            id="faq_content" 
                            name="faq_content" 
                            class="form-control large-field" 
                            rows="6" 
                            placeholder="FAQ content for Schema.org FAQ markup..."><?php echo $schema_settings['faq_content'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <button type="button" id="generate_faq_ai" class="btn btn-success btn-block" onclick="generateFAQAI()">
                            <i class="fa fa-question-circle"></i> Generate FAQ
                        </button>
                        <div class="token-info-box" style="margin-top: 8px;">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> FAQ questions & answers<br>
                                <i class="fa fa-list-ol"></i> Based on product details
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="howto_content">
                        <?php echo $entry_howto_content; ?>
                    </label>
                    <div class="col-sm-6 col-xs-12">
                        <textarea 
                            id="howto_content" 
                            name="howto_content" 
                            class="form-control large-field" 
                            rows="6" 
                            placeholder="HowTo instructions for Schema.org HowTo markup..."><?php echo $schema_settings['howto_content'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <button type="button" id="generate_howto_ai" class="btn btn-success btn-block" onclick="generateHowToAI()">
                            <i class="fa fa-list-ol"></i> Generate HowTo
                        </button>
                        <div class="token-info-box" style="margin-top: 8px;">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Step-by-step instructions<br>
                                <i class="fa fa-wrench"></i> Usage and setup guide
                            </small>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php include('reviews_section.tpl'); ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-code"></i> Additional Schema Properties
                    <small class="text-muted">- Editable shipping & return policy defaults</small>
                </h4>
            </div>
            <div class="panel-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> <strong>This field contains default shippingDetails and hasMerchantReturnPolicy</strong> 
                    that apply to ALL product variants. You can customize the shipping rates, delivery times, and return policies here.
                </div>
                
                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="others_content">
                        Additional Properties:<br>
                        <span class="help">JSON data for shippingDetails, hasMerchantReturnPolicy, productGroupID, etc.</span>
                    </label>
                    <div class="col-sm-6 col-xs-12">
                        <textarea 
                            id="others_content" 
                            name="others_content" 
                            class="form-control large-field" 
                            rows="12" 
                            placeholder='Click "Auto-Generate Defaults" to populate with shipping & return policy defaults'><?php echo $schema_settings['others_content'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <div class="btn-group-vertical btn-block">
                            <button type="button" id="auto_generate_others" class="btn btn-primary" onclick="autoGenerateOthersContent()">
                                <i class="fa fa-magic"></i> Auto-Generate Defaults
                            </button>
                            <button type="button" id="validate_json" class="btn btn-warning" onclick="validateOthersContentJSON()">
                                <i class="fa fa-check-circle"></i> Validate JSON Format
                            </button>
                        </div>
                        <div class="alert alert-info" style="margin-top: 10px; padding: 10px; font-size: 11px;">
                            <strong>Default Fields:</strong><br>
                            • shippingDetails ($5.99 USD)<br>
                            • hasMerchantReturnPolicy (30 days)<br>
                            • productGroupID (main SKU)<br>
                            • additionalProperty<br>
                            <hr style="margin: 8px 0;">
                            <strong>Custom Fields:</strong><br>
                            • isCompatibleWith<br>
                            • Custom offers<br>
                            • Rich snippets data
                        </div>
                        <div id="json_validation_result" class="help-block"></div>
                    </div>
                </div>
                
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-code"></i> Schema.org Preview
                </h4>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <div class="col-xs-12">
                        <pre id="schema_preview" class="bg-light" style="max-height: 400px; overflow-y: auto; display: none;">
                            Click "Preview Schema.org" to see generated markup
                        </pre>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="panel-footer col-xs-12">
        <div class="text-center">
            <button class="btn btn-primary lock-on-click">
                <i class="fa fa-save fa-fw"></i> <?php echo $form['submit']->text; ?>
            </button>
            <button class="btn btn-default" type="button" onclick="window.location='<?php echo $cancel; ?>'">
                <i class="fa fa-arrow-left fa-fw"></i> <?php echo $form['cancel']->text; ?>
            </button>
        </div>
    </div>

    </form>

</div>

<div class="modal fade" id="loading_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <i class="fa fa-spinner fa-spin fa-3x"></i>
                <p class="mt15" id="loading_message">Processing AI request...</p>
                <div id="loading_progress" class="progress" style="margin-top: 15px;">
                    <div class="progress-bar progress-bar-striped active" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="debug_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Debug Information</h4>
            </div>
            <div class="modal-body">
                <pre id="debug_content" style="max-height: 400px; overflow-y: auto;"></pre>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="review_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="review_modal_title">Edit Review</h4>
            </div>
            <div class="modal-body">
                <form id="review_form">
                    <input type="hidden" id="review_id" name="review_id">
                    <input type="hidden" id="product_id_review" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <div class="form-group">
                        <label for="review_author">Author:</label>
                        <input type="text" class="form-control" id="review_author" name="author" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="review_text">Review Text:</label>
                        <textarea class="form-control" id="review_text" name="text" rows="6" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="review_rating">Rating:</label>
                        <select class="form-control" id="review_rating" name="rating" required>
                            <option value="1">1 Star</option>
                            <option value="2">2 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="5">5 Stars</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="review_verified" name="verified_purchase" value="1">
                            Verified Purchase
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="review_status" name="status" value="1">
                            Active Status
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveReview()">Save Review</button>
            </div>
        </div>
    </div>
</div>

<style>
.highlight-success {
    border: 2px solid #5cb85c !important;
    background-color: #f0fff0 !important;
    transition: all 0.3s ease;
}
.highlight-error {
    border: 2px solid #d9534f !important;
    background-color: #fff0f0 !important;
    transition: all 0.3s ease;
}
.token-info-box {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 8px;
    margin-top: 5px;
}
.review-row {
    border-bottom: 1px solid #eee;
    padding: 10px 0;
}
.review-row:last-child {
    border-bottom: none;
}
.star-rating {
    color: #ffc107;
}
.btn-optimize {
    padding: 2px 6px;
    font-size: 11px;
    margin-left: 5px;
}
</style>

<script type="text/javascript">

var aiTokenSettings = {
    maxTokens: 800,
    minTokens: 100
};

$(document).ready(function() {
    console.log('=== INICIALIZANDO SMART SEO SCHEMA - GENERACIÓN INDEPENDIENTE ===');
    
    initializeTokenSettings();
    checkAIStatus();
    
    if ($('#enable_variants').is(':checked')) {
        loadVariantsPreview();
    }
    
    $('#enable_variants').change(function() {
        if ($(this).is(':checked')) {
            loadVariantsPreview();
        } else {
            $('#variants_preview').hide();
        }
    });
});

function initializeTokenSettings() {
    console.log('=== INICIALIZANDO CONFIGURACIÓN DE TOKENS INDIVIDUALES ===');
    
    aiTokenSettings.maxTokens = 800;
    aiTokenSettings.minTokens = 100;
    
    $('#max_tokens_display').text('Max Tokens: ' + aiTokenSettings.maxTokens);
    $('#min_tokens_display').text('Min Tokens: ' + aiTokenSettings.minTokens);
    
    console.log('Token settings inicializados:', aiTokenSettings);
}

function checkAIStatus() {
    var apiKey = '<?php echo addslashes($smart_seo_schema_groq_api_key); ?>';
    console.log('=== AI STATUS CHECK ===');
    console.log('API Key configured:', apiKey.length > 0);
    
    if (!apiKey || apiKey.length < 10) {
        showAIStatus('warning', 'AI features require a valid Groq API key. Configure it in extension settings.');
        $('.btn-success[id*="generate_"]').prop('disabled', true);
    } else {
        showAIStatus('success', 'API Key configured. Individual generation ready for each content type.');
    }
}

function showAIStatus(type, message) {
    var alertClass = 'alert-' + (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger'));
    $('#ai_status_alert').removeClass('alert-info alert-success alert-warning alert-danger').addClass(alertClass).show();
    $('#ai_status_message').text(message);
}

function generateDescriptionAI() {
    console.log('=== GENERANDO DESCRIPCIÓN CON IA INDIVIDUAL ===');
    
    var $button = $('#generate_description_ai');
    var originalText = $button.html();
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');
    
    $('#loading_message').text('Generating product description...');
    $('#loading_modal').modal('show');
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/generateDescriptionContent", "&product_id=" . $product_id); ?>',
        type: 'GET',
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            $('#loading_modal').modal('hide');
            
            if (response.error) {
                error_alert('Error generating description: ' + response.message);
            } else {
                $('#custom_description').val(response.content);
                $('#custom_description').addClass('highlight-success');
                setTimeout(function() {
                    $('#custom_description').removeClass('highlight-success');
                }, 3000);
                success_alert('Description generated successfully! Length: ' + response.content.length + ' characters');
            }
        },
        error: function(xhr, status, error) {
            $('#loading_modal').modal('hide');
            error_alert('Failed to generate description. Please try again.');
        },
        complete: function() {
            $button.prop('disabled', false).html(originalText);
        }
    });
}

function generateFAQAI() {
    console.log('=== GENERANDO FAQ CON IA INDIVIDUAL ===');
    
    var $button = $('#generate_faq_ai');
    var originalText = $button.html();
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');
    
    $('#loading_message').text('Generating FAQ content...');
    $('#loading_modal').modal('show');
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/generateFAQContent", "&product_id=" . $product_id); ?>',
        type: 'GET',
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            $('#loading_modal').modal('hide');
            
            if (response.error) {
                error_alert('Error generating FAQ: ' + response.message);
            } else {
                $('#faq_content').val(response.content);
                $('#faq_content').addClass('highlight-success');
                setTimeout(function() {
                    $('#faq_content').removeClass('highlight-success');
                }, 3000);
                success_alert('FAQ content generated successfully!');
            }
        },
        error: function(xhr, status, error) {
            $('#loading_modal').modal('hide');
            error_alert('Failed to generate FAQ. Please try again.');
        },
        complete: function() {
            $button.prop('disabled', false).html(originalText);
        }
    });
}

function generateHowToAI() {
    console.log('=== GENERANDO HOWTO CON IA INDIVIDUAL ===');
    
    var $button = $('#generate_howto_ai');
    var originalText = $button.html();
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');
    
    $('#loading_message').text('Generating HowTo instructions...');
    $('#loading_modal').modal('show');
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/generateHowToContent", "&product_id=" . $product_id); ?>',
        type: 'GET',
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            $('#loading_modal').modal('hide');
            
            if (response.error) {
                error_alert('Error generating HowTo: ' + response.message);
            } else {
                $('#howto_content').val(response.content);
                $('#howto_content').addClass('highlight-success');
                setTimeout(function() {
                    $('#howto_content').removeClass('highlight-success');
                }, 3000);
                success_alert('HowTo instructions generated successfully!');
            }
        },
        error: function(xhr, status, error) {
            $('#loading_modal').modal('hide');
            error_alert('Failed to generate HowTo. Please try again.');
        },
        complete: function() {
            $button.prop('disabled', false).html(originalText);
        }
    });
}

function testAIConnection() {
    console.log('=== TESTING AI CONNECTION ===');
    
    $('#test_ai_connection').attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Testing...');
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/testAIConnection", "&product_id=" . $product_id); ?>',
        type: 'GET',
        dataType: 'json',
        timeout: 15000,
        success: function(response) {
            console.log('AI test response:', response);
            
            if (response.error) {
                showAIStatus('danger', response.message);
                error_alert(response.message);
            } else {
                showAIStatus('success', response.message + ' Individual generation ready.');
                success_alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', {xhr: xhr, status: status, error: error});
            
            var errorMsg = 'Connection test failed: ';
            if (status === 'timeout') {
                errorMsg += 'Request timeout. API may be slow or unreachable.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg += xhr.responseJSON.message;
            } else {
                errorMsg += error + ' (Status: ' + status + ')';
            }
            
            showAIStatus('danger', errorMsg);
            error_alert(errorMsg);
        },
        complete: function() {
            $('#test_ai_connection').attr('disabled', false).html('<i class="fa fa-flask fa-lg"></i> <?php echo $button_test_ai_connection; ?>');
        }
    });
}

function autoGenerateOthersContent() {
    console.log('=== AUTO-GENERANDO OTHERS CONTENT CON DEFAULTS ===');
    
    $('#auto_generate_others').attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/generateOthersContent", "&product_id=" . $product_id); ?>',
        type: 'GET',
        dataType: 'json',
        timeout: 15000,
        success: function(response) {
            console.log('Others content response:', response);
            
            if (response.error) {
                error_alert('Error generating others content: ' + response.message);
            } else {
                var formattedJson = JSON.stringify(response.others_content, null, 2);
                $('#others_content').val(formattedJson);
                
                $('#others_content').addClass('highlight-success');
                setTimeout(function() {
                    $('#others_content').removeClass('highlight-success');
                }, 3000);
                
                $('html, body').animate({
                    scrollTop: $('#others_content').offset().top - 100
                }, 500);
                
                success_alert('Default shipping and return policy content generated successfully!');
            }
        },
        error: function(xhr, status, error) {
            console.log('Error generating others content:', {xhr: xhr, status: status, error: error});
            error_alert('Failed to generate others content. Please try again.');
        },
        complete: function() {
            $('#auto_generate_others').attr('disabled', false).html('<i class="fa fa-magic"></i> Auto-Generate Defaults');
        }
    });
}

function previewSchema() {
    console.log('=== GENERATING SCHEMA PREVIEW ===');
    
    $('#preview_schema').attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/previewSchema", "&product_id=" . $product_id); ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                error_alert(response.message);
            } else {
                var formattedJson = JSON.stringify(response.schema, null, 2);
                $('#schema_preview').text(formattedJson).show();
                
                $('html, body').animate({
                    scrollTop: $("#schema_preview").offset().top - 100
                }, 500);
            }
        },
        error: function() {
            error_alert('Schema preview failed. Please try again.');
        },
        complete: function() {
            $('#preview_schema').attr('disabled', false).html('<i class="fa fa-eye fa-lg"></i> <?php echo $button_preview_schema; ?>');
        }
    });
}

function loadVariantsPreview() {
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/getVariants", "&product_id=" . $product_id); ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.variants && response.variants.length > 0) {
                var html = '<h5>Found ' + response.variants.length + ' variants:</h5><ul class="list-unstyled">';
                for (var i = 0; i < response.variants.length; i++) {
                    var variant = response.variants[i];
                    html += '<li><i class="fa fa-check-circle text-success"></i> ' + 
                           variant.variant_name + ' (SKU: ' + (variant.sku || 'N/A') + ')</li>';
                }
                html += '</ul>';
                $('#variants_list').html(html);
                $('#variants_preview').show();
            } else {
                $('#variants_list').html('<p class="text-muted"><i class="fa fa-info-circle"></i> No variants found for this product.</p>');
                $('#variants_preview').show();
            }
        },
        error: function() {
            $('#variants_list').html('<p class="text-danger"><i class="fa fa-exclamation-triangle"></i> Error loading variants.</p>');
            $('#variants_preview').show();
        }
    });
}

function validateOthersContentJSON() {
    var jsonText = $('#others_content').val().trim();
    var resultDiv = $('#json_validation_result');
    
    if (jsonText === '') {
        resultDiv.html('<span class="text-info"><i class="fa fa-info-circle"></i> JSON field is empty - this is OK.</span>');
        return;
    }
    
    try {
        var parsed = JSON.parse(jsonText);
        resultDiv.html('<span class="text-success"><i class="fa fa-check-circle"></i> Valid JSON format! Ready for Schema.org enhancement.</span>');
        $('#others_content').removeClass('highlight-error').addClass('highlight-success');
        
        setTimeout(function() {
            $('#others_content').removeClass('highlight-success');
        }, 3000);
        
    } catch (e) {
        resultDiv.html('<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Invalid JSON: ' + e.message + '</span>');
        $('#others_content').removeClass('highlight-success').addClass('highlight-error');
        
        setTimeout(function() {
            $('#others_content').removeClass('highlight-error');
        }, 5000);
    }
}

function optimizeReview(reviewId, currentText) {
    console.log('=== OPTIMIZING REVIEW ===', reviewId);
    
    var $button = $('#optimize_btn_' + reviewId);
    var originalText = $button.html();
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/optimizeReview", "&product_id=" . $product_id); ?>',
        type: 'POST',
        data: {
            review_id: reviewId,
            review_text: currentText
        },
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            if (response.error) {
                error_alert('Error optimizing review: ' + response.message);
            } else {
                $('#review_text_' + reviewId).val(response.optimized_text);
                $('#review_text_' + reviewId).addClass('highlight-success');
                setTimeout(function() {
                    $('#review_text_' + reviewId).removeClass('highlight-success');
                }, 3000);
                success_alert('Review optimized successfully! Length: ' + response.optimized_length + ' chars');
            }
        },
        error: function(xhr, status, error) {
            error_alert('Failed to optimize review. Please try again.');
        },
        complete: function() {
            $button.prop('disabled', false).html(originalText);
        }
    });
}

function editReview(reviewId) {
    if (reviewId === 'new') {
        $('#review_modal_title').text('Add New Review');
        $('#review_form')[0].reset();
        $('#review_id').val('');
        $('#review_status').prop('checked', true);
    } else {
        $('#review_modal_title').text('Edit Review');
        $('#review_id').val(reviewId);
        $('#review_author').val($('#review_author_' + reviewId).text());
        $('#review_text').val($('#review_text_' + reviewId).val());
        $('#review_rating').val($('#review_rating_' + reviewId).data('rating'));
        $('#review_verified').prop('checked', $('#review_verified_' + reviewId).data('verified') == '1');
        $('#review_status').prop('checked', $('#review_status_' + reviewId).data('status') == '1');
    }
    
    $('#review_modal').modal('show');
}

function saveReview() {
    var formData = {
        review_id: $('#review_id').val(),
        product_id: $('#product_id_review').val(),
        author: $('#review_author').val(),
        text: $('#review_text').val(),
        rating: $('#review_rating').val(),
        verified_purchase: $('#review_verified').is(':checked') ? 1 : 0,
        status: $('#review_status').is(':checked') ? 1 : 0
    };
    
    if (!formData.author || !formData.text || !formData.rating) {
        error_alert('Please fill in all required fields.');
        return;
    }
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/saveReview", "&product_id=" . $product_id); ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                error_alert('Error saving review: ' + response.message);
            } else {
                $('#review_modal').modal('hide');
                success_alert(response.message);
                location.reload();
            }
        },
        error: function() {
            error_alert('Failed to save review. Please try again.');
        }
    });
}

function deleteReview(reviewId) {
    if (!confirm('Are you sure you want to delete this review?')) {
        return;
    }
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/deleteReview", "&product_id=" . $product_id); ?>',
        type: 'POST',
        data: { review_id: reviewId },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                error_alert('Error deleting review: ' + response.message);
            } else {
                success_alert(response.message);
                $('#review_row_' + reviewId).fadeOut(function() {
                    $(this).remove();
                });
            }
        },
        error: function() {
            error_alert('Failed to delete review. Please try again.');
        }
    });
}

function generateExampleReview() {
    console.log('=== GENERATING EXAMPLE REVIEW ===');
    
    $('#generate_example_review').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/generateExampleReview", "&product_id=" . $product_id); ?>',
        type: 'GET',
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            if (response.error) {
                error_alert('Error generating example review: ' + response.message);
            } else {
                var review = response.review;
                $('#review_modal_title').text('AI Generated Example Review');
                $('#review_form')[0].reset();
                $('#review_id').val('');
                $('#review_author').val(review.author);
                $('#review_text').val(review.text);
                $('#review_rating').val(review.rating);
                $('#review_verified').prop('checked', review.verified_purchase == 1);
                $('#review_status').prop('checked', review.status == 1);
                $('#review_modal').modal('show');
            }
        },
        error: function() {
            error_alert('Failed to generate example review. Please try again.');
        },
        complete: function() {
            $('#generate_example_review').prop('disabled', false).html('<i class="fa fa-star"></i> Generate Example Review');
        }
    });
}

$('#test_ai_connection').click(testAIConnection);
$('#preview_schema').click(previewSchema);
$('#generate_others_content').click(autoGenerateOthersContent);

</script>