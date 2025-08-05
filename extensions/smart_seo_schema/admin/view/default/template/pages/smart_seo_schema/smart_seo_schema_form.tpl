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
                    <strong><i class="fa fa-calculator"></i> Smart Token Division:</strong><br>
                    <span id="token_division_info">Configure your max tokens in extension settings. Tokens will be automatically divided among selected content types.</span>
                </div>
                <div class="col-md-4 text-right">
                    <strong>Current Settings:</strong><br>
                    <span class="label label-info" id="max_tokens_display">Max Tokens: Loading...</span><br>
                    <span class="label label-success" id="per_content_display" style="margin-top: 3px;">Per Content: Calculating...</span>
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
                    <div class="input-group afield col-sm-7 col-xs-12">
                        <span class="input-group-addon">
                            <input type="checkbox" id="ai_generate_description" name="ai_generate_description" value="1" onchange="updateTokenDivision()">
                            <label for="ai_generate_description" style="margin-left: 5px;">AI Generate</label>
                        </span>
                        <textarea 
                            id="custom_description" 
                            name="custom_description" 
                            class="form-control large-field" 
                            rows="4" 
                            placeholder="Select AI Generate and click main button for optimized content..."><?php echo $schema_settings['custom_description'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-sm-2 col-xs-12">
                        <div class="token-info-box" id="description_token_info" style="display: none;">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Tokens: <span class="token-count">0</span><br>
                                <i class="fa fa-clock-o"></i> Est. words: <span class="word-estimate">0</span>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="enable_variants">
                        <?php echo $entry_enable_variants; ?>
                    </label>
                    <div class="input-group afield col-sm-7 col-xs-12">
                        <?php echo $form['fields']['enable_variants']; ?>
                    </div>
                </div>

                <div id="variants_preview" class="form-group" style="display: none;">
                    <label class="control-label col-sm-3 col-xs-12">Product Variants Found:</label>
                    <div class="col-sm-7 col-xs-12">
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
                    <small class="text-muted">- Smart Token Division Active</small>
                </h4>
            </div>
            <div class="panel-body">

                <div class="row" id="token_division_summary" style="display: none; margin-bottom: 20px;">
                    <div class="col-xs-12">
                        <div class="alert alert-warning">
                            <strong><i class="fa fa-pie-chart"></i> Token Distribution:</strong>
                            <div id="token_breakdown" class="row" style="margin-top: 10px;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="faq_content">
                        <?php echo $entry_faq_content; ?>
                    </label>
                    <div class="input-group afield col-sm-7 col-xs-12">
                        <span class="input-group-addon">
                            <input type="checkbox" id="ai_generate_faq" name="ai_generate_faq" value="1" onchange="updateTokenDivision()">
                            <label for="ai_generate_faq" style="margin-left: 5px;">AI Generate</label>
                        </span>
                        <textarea 
                            id="faq_content" 
                            name="faq_content" 
                            class="form-control large-field" 
                            rows="6" 
                            placeholder="Select AI Generate for FAQ with optimal token allocation..."><?php echo $schema_settings['faq_content'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-sm-2 col-xs-12">
                        <div class="token-info-box" id="faq_token_info" style="display: none;">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Tokens: <span class="token-count">0</span><br>
                                <i class="fa fa-clock-o"></i> Est. words: <span class="word-estimate">0</span>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="howto_content">
                        <?php echo $entry_howto_content; ?>
                    </label>
                    <div class="input-group afield col-sm-7 col-xs-12">
                        <span class="input-group-addon">
                            <input type="checkbox" id="ai_generate_howto" name="ai_generate_howto" value="1" onchange="updateTokenDivision()">
                            <label for="ai_generate_howto" style="margin-left: 5px;">AI Generate</label>
                        </span>
                        <textarea 
                            id="howto_content" 
                            name="howto_content" 
                            class="form-control large-field" 
                            rows="6" 
                            placeholder="Select AI Generate for HowTo with smart token management..."><?php echo $schema_settings['howto_content'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-sm-2 col-xs-12">
                        <div class="token-info-box" id="howto_token_info" style="display: none;">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Tokens: <span class="token-count">0</span><br>
                                <i class="fa fa-clock-o"></i> Est. words: <span class="word-estimate">0</span>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-7">
                        <button type="button" id="generate_ai_content_main" class="btn btn-success btn-lg" onclick="generateAllAIContent()">
                            <i class="fa fa-magic"></i> Generate Selected AI Content
                            <small id="generate_button_info" style="display: block; margin-top: 5px;">
                                Smart token division will optimize content length automatically
                            </small>
                        </button>
                        <p class="help-block">
                            <strong>How it works:</strong> Your total max tokens are automatically divided among selected content types. 
                            Each type gets an equal share, ensuring optimal content length and API efficiency.
                            <br><small class="text-muted">Minimum 100 tokens guaranteed per content type.</small>
                        </p>
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
                    <div class="input-group afield col-sm-7 col-xs-12">
                        <textarea 
                            id="others_content" 
                            name="others_content" 
                            class="form-control large-field" 
                            rows="12" 
                            placeholder='Click "Auto-Generate Defaults" to populate with shipping & return policy defaults'><?php echo $schema_settings['others_content'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-sm-2 col-xs-12">
                        <div class="alert alert-info" style="margin-top: 0; padding: 10px;">
                            <small>
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
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-7">
                        <div class="btn-group">
                            <button type="button" id="auto_generate_others" class="btn btn-primary" onclick="autoGenerateOthersContent()">
                                <i class="fa fa-magic"></i> Auto-Generate Defaults
                            </button>
                            <button type="button" id="validate_json" class="btn btn-warning" onclick="validateOthersContentJSON()">
                                <i class="fa fa-check-circle"></i> Validate JSON Format
                            </button>
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
                <p class="mt15">Processing AI request with smart token division...</p>
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
                <h4 class="modal-title">Debug Information & Token Analysis</h4>
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
.token-breakdown-item {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
    margin-bottom: 5px;
    text-align: center;
}
.token-breakdown-item.active {
    border-color: #28a745;
    background-color: #f8fff9;
}
#generate_button_info {
    font-size: 11px;
    color: #ffffff;
    opacity: 0.9;
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

var globalTokenSettings = {
    maxTokens: 800,
    minTokensPerContent: 100,
    selectedContentTypes: []
};

$(document).ready(function() {
    console.log('=== INICIALIZANDO SMART SEO SCHEMA CON DIVISIÓN DE TOKENS ===');
    
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
    
    updateTokenDivision();
    
    setTimeout(function() {
        debugFormFields();
    }, 1000);
});

function initializeTokenSettings() {
    console.log('=== INICIALIZANDO CONFIGURACIÓN DE TOKENS ===');
    
    globalTokenSettings.maxTokens = 800;
    
    $('#max_tokens_display').text('Max Tokens: ' + globalTokenSettings.maxTokens);
    
    console.log('Token settings inicializados:', globalTokenSettings);
}

function updateTokenDivision() {
    console.log('=== ACTUALIZANDO DIVISIÓN DE TOKENS ===');
    
    var selectedTypes = [];
    if ($('#ai_generate_description').is(':checked')) selectedTypes.push('description');
    if ($('#ai_generate_faq').is(':checked')) selectedTypes.push('faq');
    if ($('#ai_generate_howto').is(':checked')) selectedTypes.push('howto');
    
    globalTokenSettings.selectedContentTypes = selectedTypes;
    
    console.log('Tipos seleccionados:', selectedTypes);
    
    if (selectedTypes.length === 0) {
        $('#per_content_display').text('Per Content: Select types above');
        $('#token_division_summary').hide();
        hideAllTokenInfo();
        updateGenerateButtonInfo(0, 0);
        return;
    }
    
    var tokensPerContent = Math.floor(globalTokenSettings.maxTokens / selectedTypes.length);
    var actualTotal = globalTokenSettings.maxTokens;
    
    if (tokensPerContent < globalTokenSettings.minTokensPerContent) {
        tokensPerContent = globalTokenSettings.minTokensPerContent;
        actualTotal = tokensPerContent * selectedTypes.length;
        console.log('Ajuste por mínimo: ', tokensPerContent, 'tokens por tipo, total:', actualTotal);
    }
    
    $('#per_content_display').text('Per Content: ' + tokensPerContent + ' tokens');
    updateGenerateButtonInfo(selectedTypes.length, tokensPerContent);
    
    showTokenBreakdown(selectedTypes, tokensPerContent, actualTotal);
    
    updateIndividualTokenInfo(selectedTypes, tokensPerContent);
    
    console.log('División calculada - Por contenido:', tokensPerContent, 'Total:', actualTotal);
}

function showTokenBreakdown(selectedTypes, tokensPerContent, totalTokens) {
    var breakdownHtml = '';
    
    selectedTypes.forEach(function(type) {
        var typeLabel = type.charAt(0).toUpperCase() + type.slice(1);
        var estimatedWords = Math.floor(tokensPerContent * 0.75);
        
        breakdownHtml += '<div class="col-md-3 col-sm-6">' +
            '<div class="token-breakdown-item active">' +
            '<strong>' + typeLabel + '</strong><br>' +
            '<span class="text-success">' + tokensPerContent + ' tokens</span><br>' +
            '<small class="text-muted">~' + estimatedWords + ' words</small>' +
            '</div>' +
            '</div>';
    });
    
    $('#token_breakdown').html(breakdownHtml);
    $('#token_division_summary').show();
}

function updateIndividualTokenInfo(selectedTypes, tokensPerContent) {
    hideAllTokenInfo();
    
    selectedTypes.forEach(function(type) {
        var infoBoxId = type + '_token_info';
        var estimatedWords = Math.floor(tokensPerContent * 0.75);
        
        $('#' + infoBoxId + ' .token-count').text(tokensPerContent);
        $('#' + infoBoxId + ' .word-estimate').text(estimatedWords);
        $('#' + infoBoxId).show();
    });
}

function hideAllTokenInfo() {
    $('.token-info-box').hide();
}

function updateGenerateButtonInfo(count, tokensPerContent) {
    var infoText = '';
    
    if (count === 0) {
        infoText = 'Select content types above to see token allocation';
    } else if (count === 1) {
        infoText = 'Full ' + globalTokenSettings.maxTokens + ' tokens for selected content';
    } else {
        infoText = count + ' contents × ' + tokensPerContent + ' tokens each = efficient generation';
    }
    
    $('#generate_button_info').text(infoText);
}

function checkAIStatus() {
    var apiKey = '<?php echo addslashes($smart_seo_schema_groq_api_key); ?>';
    console.log('=== AI STATUS CHECK ===');
    console.log('API Key length:', apiKey.length);
    console.log('API Key configured:', apiKey.length > 0);
    
    if (!apiKey || apiKey.length < 10) {
        showAIStatus('warning', 'AI features require a valid Groq API key. Configure it in extension settings.');
        $('#test_ai_connection, #generate_ai_content_main').prop('disabled', true);
    } else {
        showAIStatus('success', 'API Key configured. Token division ready. Click "Test AI Connection" to verify.');
    }
}

function showAIStatus(type, message) {
    var alertClass = 'alert-' + (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger'));
    $('#ai_status_alert').removeClass('alert-info alert-success alert-warning alert-danger').addClass(alertClass).show();
    $('#ai_status_message').text(message);
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
                
                if (response.debug) {
                    $('#debug_content').text(JSON.stringify(response.debug, null, 2));
                    $('#debug_modal').modal('show');
                }
            } else {
                showAIStatus('success', response.message + ' Token division system ready.');
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
            
            $('#debug_content').text('Status: ' + status + '\nError: ' + error + '\nResponse: ' + xhr.responseText);
            $('#debug_modal').modal('show');
        },
        complete: function() {
            $('#test_ai_connection').attr('disabled', false).html('<i class="fa fa-flask fa-lg"></i> <?php echo $button_test_ai_connection; ?>');
        }
    });
}

function generateAllAIContent() {
    console.log('=== GENERACIÓN MÚLTIPLE CON DIVISIÓN INTELIGENTE DE TOKENS ===');
    
    var selectedTypes = globalTokenSettings.selectedContentTypes;
    
    console.log('Tipos seleccionados:', selectedTypes);
    console.log('Configuración de tokens:', globalTokenSettings);
    
    if (selectedTypes.length === 0) {
        error_alert('Please select at least one content type to generate.');
        return;
    }
    
    var tokensPerContent = Math.floor(globalTokenSettings.maxTokens / selectedTypes.length);
    if (tokensPerContent < globalTokenSettings.minTokensPerContent) {
        tokensPerContent = globalTokenSettings.minTokensPerContent;
    }
    
    var $button = $('#generate_ai_content_main');
    var originalText = $button.html();
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating with Smart Token Division...');
    
    $('#loading_modal .modal-body p').html('Generating ' + selectedTypes.length + ' content types<br>' +
                                          '<strong>' + tokensPerContent + ' tokens each</strong><br>' +
                                          '<small>Total: ' + (tokensPerContent * selectedTypes.length) + ' tokens</small>');
    $('#loading_modal').modal('show');
    
    var postData = {
        'content_types': selectedTypes,
        'product_id': '<?php echo $product_id; ?>'
    };
    
    console.log('Datos a enviar:', postData);
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/generateMultipleAIContent", "&product_id=" . $product_id); ?>',
        type: 'POST',
        data: postData,
        dataType: 'json',
        timeout: 90000,
        success: function(response) {
            console.log('=== RESPUESTA CON DIVISIÓN DE TOKENS RECIBIDA ===');
            console.log('Response completo:', response);
            
            $('#loading_modal').modal('hide');
            
            if (response.error) {
                console.error('Error en respuesta:', response.message);
                error_alert('Error generating content with token division: ' + response.message);
                
                if (response.debug) {
                    console.log('Debug info:', response.debug);
                    $('#debug_content').text(JSON.stringify(response.debug, null, 2));
                    $('#debug_modal').modal('show');
                }
            } else {
                console.log('Contenido generado exitosamente con división de tokens para tipos:', Object.keys(response.content));
                
                var successCount = 0;
                var fieldMapping = {
                    'description': 'custom_description',
                    'faq': 'faq_content',
                    'howto': 'howto_content'
                };
                
                for (var contentType in response.content) {
                    var fieldId = fieldMapping[contentType];
                    if (fieldId && response.content[contentType]) {
                        $('#' + fieldId).val(response.content[contentType]);
                        
                        $('#' + fieldId).addClass('highlight-success');
                        setTimeout(function(field) {
                            return function() { $('#' + field).removeClass('highlight-success'); };
                        }(fieldId), 4000);
                        
                        successCount++;
                        
                        var content = response.content[contentType];
                        var wordCount = content.split(/\s+/).length;
                        var charCount = content.length;
                        console.log('Contenido ' + contentType + ': ' + wordCount + ' palabras, ' + charCount + ' caracteres');
                    }
                }
                
                if (successCount > 0) {
                    var successMsg = 'Successfully generated ' + successCount + ' content types with smart token division! ';
                    successMsg += 'Each content optimized for ' + tokensPerContent + ' tokens.';
                    success_alert(successMsg);
                    
                    var firstField = Object.keys(response.content)[0];
                    var firstFieldId = fieldMapping[firstField];
                    if (firstFieldId) {
                        $('html, body').animate({
                            scrollTop: $('#' + firstFieldId).offset().top - 100
                        }, 500);
                    }
                } else {
                    error_alert('No content was generated. Please try again.');
                }
            }
        },
        error: function(xhr, status, error) {
            console.log('=== ERROR AJAX CON DIVISIÓN DE TOKENS ===');
            console.log('Status:', status);
            console.log('Error:', error);
            console.log('Response Text:', xhr.responseText);
            
            $('#loading_modal').modal('hide');
            
            var errorMsg = 'Error generating content with token division: ';
            
            if (status === 'timeout') {
                errorMsg += 'Request timeout. Token division processing takes longer for better results.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg += xhr.responseJSON.message;
            } else if (xhr.responseText) {
                var tempDiv = $('<div>').html(xhr.responseText);
                var errorText = tempDiv.find('.alert-danger').text() || xhr.responseText.substring(0, 200);
                errorMsg += errorText;
            } else {
                errorMsg += error + ' (Status: ' + status + ')';
            }
            
            error_alert(errorMsg);
            
            $('#debug_content').text('AJAX Error Details (Token Division):\n' +
                'Status: ' + status + '\n' +
                'Error: ' + error + '\n' +
                'Selected Types: ' + selectedTypes.join(', ') + '\n' +
                'Tokens Per Content: ' + tokensPerContent + '\n' +
                'Response: ' + xhr.responseText);
            $('#debug_modal').modal('show');
        },
        complete: function() {
            console.log('=== AJAX CON DIVISIÓN DE TOKENS COMPLETADO ===');
            $button.prop('disabled', false).html(originalText);
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

function debugFormFields() {
    console.log('=== DEBUG CAMPOS FORMULARIO CON TOKENS ===');
    
    var expectedFields = ['custom_description', 'faq_content', 'howto_content', 'others_content'];
    
    expectedFields.forEach(function(fieldName) {
        var byId = $('#' + fieldName);
        var byName = $('[name="' + fieldName + '"]');
        
        console.log('Campo:', fieldName);
        console.log('  Por ID (#' + fieldName + '):', byId.length, byId.length > 0 ? byId[0] : 'No encontrado');
        console.log('  Por name [name="' + fieldName + '"]:', byName.length, byName.length > 0 ? byName[0] : 'No encontrado');
        console.log('  Valor actual:', byId.length > 0 ? byId.val().substring(0, 50) + '...' : 'N/A');
    });
    
    console.log('=== CONFIGURACIÓN DE TOKENS ===');
    console.log('Max tokens:', globalTokenSettings.maxTokens);
    console.log('Min tokens per content:', globalTokenSettings.minTokensPerContent);
    console.log('Selected types:', globalTokenSettings.selectedContentTypes);
    
    console.log('=== TODOS LOS CAMPOS DEL FORMULARIO ===');
    $('#smart_seo_schema_form input, #smart_seo_schema_form textarea, #smart_seo_schema_form select').each(function() {
        console.log('  -', this.tagName, 'id="' + this.id + '"', 'name="' + this.name + '"');
    });
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