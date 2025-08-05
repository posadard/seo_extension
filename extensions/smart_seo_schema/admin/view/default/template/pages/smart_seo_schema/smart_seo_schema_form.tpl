<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<?php echo $summary_form; ?>

<?php echo $product_tabs; ?>

<div id="content" class="panel panel-default">

    <div class="panel-heading col-xs-12">
        <div class="primary_content_actions pull-left">
            <div class="btn-group mr10 toolbar">
                <?php echo $this->getHookVar('common_content_buttons'); ?>

                <!-- AI Connection Test Button -->
                <button type="button" id="test_ai_connection" class="btn btn-info tooltips" 
                        title="<?php echo $button_test_ai_connection; ?>">
                    <i class="fa fa-flask fa-lg"></i> <?php echo $button_test_ai_connection; ?>
                </button>

                <!-- Schema Preview Button -->
                <button type="button" id="preview_schema" class="btn btn-success tooltips" 
                        title="<?php echo $button_preview_schema; ?>">
                    <i class="fa fa-eye fa-lg"></i> <?php echo $button_preview_schema; ?>
                </button>

                <?php echo $this->getHookVar('extension_toolbar_buttons'); ?>
            </div>
        </div>

        <?php include($tpl_common_dir . 'content_buttons.tpl'); ?>
    </div>

    <?php echo $form['form_open']; ?>
    <div class="panel-body panel-body-nopadding tab-content col-xs-12">

        <!-- Token Division Info Panel -->
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

        <!-- AI Status Alert -->
        <div id="ai_status_alert" class="alert alert-info" style="display: none;">
            <i class="fa fa-info-circle fa-fw fa-lg"></i>
            <span id="ai_status_message"></span>
        </div>

        <!-- Basic Schema Settings Section -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-cog"></i> <?php echo $text_section_basic; ?>
                </h4>
            </div>
            <div class="panel-body">
                
                <!-- Custom Description - OPTIMIZADO CON DIVISIÓN DE TOKENS -->
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

                <!-- Enable Variants -->
                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="enable_variants">
                        <?php echo $entry_enable_variants; ?>
                    </label>
                    <div class="input-group afield col-sm-7 col-xs-12">
                        <?php echo $form['fields']['enable_variants']; ?>
                    </div>
                </div>

                <!-- Variants Preview -->
                <div id="variants_preview" class="form-group" style="display: none;">
                    <label class="control-label col-sm-3 col-xs-12">Product Variants Found:</label>
                    <div class="col-sm-7 col-xs-12">
                        <div id="variants_list" class="well well-sm">
                            <!-- Variants will be loaded here via AJAX -->
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- AI Content Generation Section CON DIVISIÓN DE TOKENS -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-magic"></i> <?php echo $text_section_ai; ?> 
                    <small class="text-muted">- Smart Token Division Active</small>
                </h4>
            </div>
            <div class="panel-body">

                <!-- Token Division Summary -->
                <div class="row" id="token_division_summary" style="display: none; margin-bottom: 20px;">
                    <div class="col-xs-12">
                        <div class="alert alert-warning">
                            <strong><i class="fa fa-pie-chart"></i> Token Distribution:</strong>
                            <div id="token_breakdown" class="row" style="margin-top: 10px;">
                                <!-- Token breakdown will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Content - OPTIMIZADO CON DIVISIÓN DE TOKENS -->
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

                <!-- HowTo Content - OPTIMIZADO CON DIVISIÓN DE TOKENS -->
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

                <!-- Review Content - OPTIMIZADO CON DIVISIÓN DE TOKENS -->
                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="review_content">
                        <?php echo $entry_review_content; ?>
                    </label>
                    <div class="input-group afield col-sm-7 col-xs-12">
                        <span class="input-group-addon">
                            <input type="checkbox" id="ai_generate_review" name="ai_generate_review" value="1" onchange="updateTokenDivision()">
                            <label for="ai_generate_review" style="margin-left: 5px;">AI Generate</label>
                        </span>
                        <textarea 
                            id="review_content" 
                            name="review_content" 
                            class="form-control large-field" 
                            rows="6" 
                            placeholder="Select AI Generate for review with precise token limits..."><?php echo $schema_settings['review_content'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-sm-2 col-xs-12">
                        <div class="token-info-box" id="review_token_info" style="display: none;">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Tokens: <span class="token-count">0</span><br>
                                <i class="fa fa-clock-o"></i> Est. words: <span class="word-estimate">0</span>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Main AI Generation Button CON INFORMACIÓN DE TOKENS -->
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

        <!-- Others Content Section - NUEVO CAMPO PARA DATOS ADICIONALES -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-code"></i> Additional Schema Properties
                    <small class="text-muted">- JSON data for enhanced Rich Results</small>
                </h4>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="others_content">
                        Additional Properties:<br>
                        <span class="help">JSON data for productGroupID, additionalProperty, shippingDetails, etc.</span>
                    </label>
                    <div class="input-group afield col-sm-7 col-xs-12">
                        <textarea 
                            id="others_content" 
                            name="others_content" 
                            class="form-control large-field" 
                            rows="8" 
                            placeholder='{"productGroupID": "ABC123", "additionalProperty": [], "isCompatibleWith": []}'><?php echo $schema_settings['others_content'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-sm-2 col-xs-12">
                        <div class="alert alert-info" style="margin-top: 0; padding: 10px;">
                            <small>
                                <strong>Examples:</strong><br>
                                • productGroupID<br>
                                • additionalProperty<br>
                                • isCompatibleWith<br>
                                • Custom offers<br>
                                • Rich snippets data
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- JSON Validation Button -->
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-7">
                        <button type="button" id="validate_json" class="btn btn-warning" onclick="validateOthersContentJSON()">
                            <i class="fa fa-check-circle"></i> Validate JSON Format
                        </button>
                        <div id="json_validation_result" class="help-block"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schema Preview Section -->
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

<!-- Loading Modal -->
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

<!-- Debug Modal -->
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

<!-- CSS para highlighting y token info -->
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
</style>

<script type="text/javascript">
<!--

// Variables globales para token management
var globalTokenSettings = {
    maxTokens: 800, // Default, se actualizará desde configuración
    minTokensPerContent: 100,
    selectedContentTypes: []
};

$(document).ready(function() {
    console.log('=== INICIALIZANDO SMART SEO SCHEMA CON DIVISIÓN DE TOKENS ===');
    
    // Initialize token settings first
    initializeTokenSettings();
    
    // Check AI status on load
    checkAIStatus();
    
    // Load variants preview if enabled
    if ($('#enable_variants').is(':checked')) {
        loadVariantsPreview();
    }
    
    // Toggle variants preview
    $('#enable_variants').change(function() {
        if ($(this).is(':checked')) {
            loadVariantsPreview();
        } else {
            $('#variants_preview').hide();
        }
    });
    
    // Initial token division update
    updateTokenDivision();
    
    // Debug inicial de campos
    setTimeout(function() {
        debugFormFields();
    }, 1000);
});

function initializeTokenSettings() {
    console.log('=== INICIALIZANDO CONFIGURACIÓN DE TOKENS ===');
    
    // Simular obtención desde configuración PHP (en implementación real vendrá del backend)
    // Estos valores deberían venir del controlador PHP
    globalTokenSettings.maxTokens = 800; // Valor por defecto, debería venir de la configuración
    
    // Actualizar displays iniciales
    $('#max_tokens_display').text('Max Tokens: ' + globalTokenSettings.maxTokens);
    
    console.log('Token settings inicializados:', globalTokenSettings);
}

function updateTokenDivision() {
    console.log('=== ACTUALIZANDO DIVISIÓN DE TOKENS ===');
    
    // Obtener tipos de contenido seleccionados
    var selectedTypes = [];
    if ($('#ai_generate_description').is(':checked')) selectedTypes.push('description');
    if ($('#ai_generate_faq').is(':checked')) selectedTypes.push('faq');
    if ($('#ai_generate_howto').is(':checked')) selectedTypes.push('howto');
    if ($('#ai_generate_review').is(':checked')) selectedTypes.push('review');
    
    globalTokenSettings.selectedContentTypes = selectedTypes;
    
    console.log('Tipos seleccionados:', selectedTypes);
    
    if (selectedTypes.length === 0) {
        // No hay tipos seleccionados
        $('#per_content_display').text('Per Content: Select types above');
        $('#token_division_summary').hide();
        hideAllTokenInfo();
        updateGenerateButtonInfo(0, 0);
        return;
    }
    
    // Calcular división de tokens
    var tokensPerContent = Math.floor(globalTokenSettings.maxTokens / selectedTypes.length);
    var actualTotal = globalTokenSettings.maxTokens;
    
    // Verificar mínimo garantizado
    if (tokensPerContent < globalTokenSettings.minTokensPerContent) {
        tokensPerContent = globalTokenSettings.minTokensPerContent;
        actualTotal = tokensPerContent * selectedTypes.length;
        console.log('Ajuste por mínimo: ', tokensPerContent, 'tokens por tipo, total:', actualTotal);
    }
    
    // Actualizar displays
    $('#per_content_display').text('Per Content: ' + tokensPerContent + ' tokens');
    updateGenerateButtonInfo(selectedTypes.length, tokensPerContent);
    
    // Mostrar breakdown detallado
    showTokenBreakdown(selectedTypes, tokensPerContent, actualTotal);
    
    // Actualizar info boxes individuales
    updateIndividualTokenInfo(selectedTypes, tokensPerContent);
    
    console.log('División calculada - Por contenido:', tokensPerContent, 'Total:', actualTotal);
}

function showTokenBreakdown(selectedTypes, tokensPerContent, totalTokens) {
    var breakdownHtml = '';
    
    selectedTypes.forEach(function(type) {
        var typeLabel = type.charAt(0).toUpperCase() + type.slice(1);
        var estimatedWords = Math.floor(tokensPerContent * 0.75); // Aproximación tokens a palabras
        
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
    // Hide all token info boxes first
    hideAllTokenInfo();
    
    // Show info for selected types
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
        timeout: 15000, // 15 seconds timeout
        success: function(response) {
            console.log('AI test response:', response);
            
            if (response.error) {
                showAIStatus('danger', response.message);
                error_alert(response.message);
                
                // Show debug info if available
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
            
            // Show raw response for debugging
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
    
    // Obtener tipos de contenido seleccionados
    var selectedTypes = globalTokenSettings.selectedContentTypes;
    
    console.log('Tipos seleccionados:', selectedTypes);
    console.log('Configuración de tokens:', globalTokenSettings);
    
    if (selectedTypes.length === 0) {
        error_alert('Please select at least one content type to generate.');
        return;
    }
    
    // Calcular información de tokens para mostrar al usuario
    var tokensPerContent = Math.floor(globalTokenSettings.maxTokens / selectedTypes.length);
    if (tokensPerContent < globalTokenSettings.minTokensPerContent) {
        tokensPerContent = globalTokenSettings.minTokensPerContent;
    }
    
    // Deshabilitar botón y mostrar loading con información de tokens
    var $button = $('#generate_ai_content_main');
    var originalText = $button.html();
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating with Smart Token Division...');
    
    // Actualizar mensaje del modal
    $('#loading_modal .modal-body p').html('Generating ' + selectedTypes.length + ' content types<br>' +
                                          '<strong>' + tokensPerContent + ' tokens each</strong><br>' +
                                          '<small>Total: ' + (tokensPerContent * selectedTypes.length) + ' tokens</small>');
    $('#loading_modal').modal('show');
    
    // Preparar datos
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
        timeout: 90000, // 90 segundos para generación con división de tokens
        success: function(response) {
            console.log('=== RESPUESTA CON DIVISIÓN DE TOKENS RECIBIDA ===');
            console.log('Response completo:', response);
            
            $('#loading_modal').modal('hide');
            
            if (response.error) {
                console.error('Error en respuesta:', response.message);
                error_alert('Error generating content with token division: ' + response.message);
                
                // Show debug si está disponible
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
                    'howto': 'howto_content',
                    'review': 'review_content'
                };
                
                // Llenar campos con contenido generado
                for (var contentType in response.content) {
                    var fieldId = fieldMapping[contentType];
                    if (fieldId && response.content[contentType]) {
                        $('#' + fieldId).val(response.content[contentType]);
                        
                        // Highlight del campo con información de tokens
                        $('#' + fieldId).addClass('highlight-success');
                        setTimeout(function(field) {
                            return function() { $('#' + field).removeClass('highlight-success'); };
                        }(fieldId), 4000); // Más tiempo para apreciar el resultado
                        
                        successCount++;
                        
                        // Log de estadísticas del contenido generado
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
                    
                    // Scroll al primer campo generado
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
            
            // Mostrar respuesta completa en debug modal
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
            // Restaurar botón
            $button.prop('disabled', false).html(originalText);
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
                
                // Scroll to preview
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

// Función auxiliar para debug de campos
function debugFormFields() {
    console.log('=== DEBUG CAMPOS FORMULARIO CON TOKENS ===');
    
    var expectedFields = ['custom_description', 'faq_content', 'howto_content', 'review_content', 'others_content'];
    
    expectedFields.forEach(function(fieldName) {
        var byId = $('#' + fieldName);
        var byName = $('[name="' + fieldName + '"]');
        
        console.log('Campo:', fieldName);
        console.log('  Por ID (#' + fieldName + '):', byId.length, byId.length > 0 ? byId[0] : 'No encontrado');
        console.log('  Por name [name="' + fieldName + '"]:', byName.length, byName.length > 0 ? byName[0] : 'No encontrado');
        console.log('  Valor actual:', byId.length > 0 ? byId.val().substring(0, 50) + '...' : 'N/A');
    });
    
    // Información adicional de tokens
    console.log('=== CONFIGURACIÓN DE TOKENS ===');
    console.log('Max tokens:', globalTokenSettings.maxTokens);
    console.log('Min tokens per content:', globalTokenSettings.minTokensPerContent);
    console.log('Selected types:', globalTokenSettings.selectedContentTypes);
    
    // Listar todos los campos del formulario
    console.log('=== TODOS LOS CAMPOS DEL FORMULARIO ===');
    $('#smart_seo_schema_form input, #smart_seo_schema_form textarea, #smart_seo_schema_form select').each(function() {
        console.log('  -', this.tagName, 'id="' + this.id + '"', 'name="' + this.name + '"');
    });
}

// Bind click events
$('#test_ai_connection').click(testAIConnection);
$('#preview_schema').click(previewSchema);

-->
</script>