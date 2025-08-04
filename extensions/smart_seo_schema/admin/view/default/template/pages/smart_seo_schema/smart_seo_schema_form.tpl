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

        <!-- Debug Info -->
        <div class="alert alert-info">
            <strong>Debug Info:</strong><br>
            API Key Status: <?php echo !empty($smart_seo_schema_groq_api_key) ? 'Configured (' . strlen($smart_seo_schema_groq_api_key) . ' chars)' : 'Not configured'; ?><br>
            Debug Log: <code>/system/logs/smart_seo_schema_debug.log</code><br>
            Product ID: <?php echo $product_id; ?>
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
                
                <!-- Custom Description - OPTIMIZADO -->
                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="custom_description">
                        <?php echo $entry_custom_description; ?>
                    </label>
                    <div class="input-group afield col-sm-7 col-xs-12">
                        <span class="input-group-addon">
                            <input type="checkbox" id="ai_generate_description" name="ai_generate_description" value="1">
                            <label for="ai_generate_description" style="margin-left: 5px;">AI Generate</label>
                        </span>
                        <textarea 
                            id="custom_description" 
                            name="custom_description" 
                            class="form-control large-field" 
                            rows="4" 
                            placeholder="Mark 'AI Generate' checkbox and click main AI button..."><?php echo $schema_settings['custom_description'] ?? ''; ?></textarea>
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

        <!-- AI Content Generation Section -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-magic"></i> <?php echo $text_section_ai; ?>
                </h4>
            </div>
            <div class="panel-body">

                <!-- FAQ Content - OPTIMIZADO -->
                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="faq_content">
                        <?php echo $entry_faq_content; ?>
                    </label>
                    <div class="input-group afield col-sm-7 col-xs-12">
                        <span class="input-group-addon">
                            <input type="checkbox" id="ai_generate_faq" name="ai_generate_faq" value="1">
                            <label for="ai_generate_faq" style="margin-left: 5px;">AI Generate</label>
                        </span>
                        <textarea 
                            id="faq_content" 
                            name="faq_content" 
                            class="form-control large-field" 
                            rows="6" 
                            placeholder="Mark 'AI Generate' checkbox and click main AI button..."><?php echo $schema_settings['faq_content'] ?? ''; ?></textarea>
                    </div>
                </div>

                <!-- HowTo Content - OPTIMIZADO -->
                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="howto_content">
                        <?php echo $entry_howto_content; ?>
                    </label>
                    <div class="input-group afield col-sm-7 col-xs-12">
                        <span class="input-group-addon">
                            <input type="checkbox" id="ai_generate_howto" name="ai_generate_howto" value="1">
                            <label for="ai_generate_howto" style="margin-left: 5px;">AI Generate</label>
                        </span>
                        <textarea 
                            id="howto_content" 
                            name="howto_content" 
                            class="form-control large-field" 
                            rows="6" 
                            placeholder="Mark 'AI Generate' checkbox and click main AI button..."><?php echo $schema_settings['howto_content'] ?? ''; ?></textarea>
                    </div>
                </div>

                <!-- Review Content - OPTIMIZADO -->
                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="review_content">
                        <?php echo $entry_review_content; ?>
                    </label>
                    <div class="input-group afield col-sm-7 col-xs-12">
                        <span class="input-group-addon">
                            <input type="checkbox" id="ai_generate_review" name="ai_generate_review" value="1">
                            <label for="ai_generate_review" style="margin-left: 5px;">AI Generate</label>
                        </span>
                        <textarea 
                            id="review_content" 
                            name="review_content" 
                            class="form-control large-field" 
                            rows="6" 
                            placeholder="Mark 'AI Generate' checkbox and click main AI button..."><?php echo $schema_settings['review_content'] ?? ''; ?></textarea>
                    </div>
                </div>

                <!-- Main AI Generation Button -->
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-7">
                        <button type="button" id="generate_ai_content_main" class="btn btn-success btn-lg" onclick="generateAllAIContent()">
                            <i class="fa fa-magic"></i> Generate Selected AI Content
                        </button>
                        <p class="help-block">Select content types above with checkboxes, then click this button to generate all selected content in one request.</p>
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
                <p class="mt15">Processing AI request...</p>
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
                <h4 class="modal-title">Debug Information</h4>
            </div>
            <div class="modal-body">
                <pre id="debug_content" style="max-height: 400px; overflow-y: auto;"></pre>
            </div>
        </div>
    </div>
</div>

<!-- CSS para highlighting -->
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
</style>

<script type="text/javascript">
<!--

$(document).ready(function() {
    console.log('=== INICIALIZANDO SMART SEO SCHEMA ===');
    
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
    
    // Debug inicial de campos
    setTimeout(function() {
        debugFormFields();
    }, 1000);
});

function checkAIStatus() {
    var apiKey = '<?php echo addslashes($smart_seo_schema_groq_api_key); ?>';
    console.log('=== AI STATUS CHECK ===');
    console.log('API Key length:', apiKey.length);
    console.log('API Key configured:', apiKey.length > 0);
    
    if (!apiKey || apiKey.length < 10) {
        showAIStatus('warning', 'AI features require a valid Groq API key. Configure it in extension settings.');
        $('#test_ai_connection, #generate_ai_content_main').prop('disabled', true);
    } else {
        showAIStatus('success', 'API Key configured. Click "Test AI Connection" to verify.');
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
                showAIStatus('success', response.message);
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
    console.log('=== GENERACIÓN MÚLTIPLE DE CONTENIDO IA ===');
    
    // Obtener tipos de contenido seleccionados
    var selectedTypes = [];
    if ($('#ai_generate_description').is(':checked')) selectedTypes.push('description');
    if ($('#ai_generate_faq').is(':checked')) selectedTypes.push('faq');
    if ($('#ai_generate_howto').is(':checked')) selectedTypes.push('howto');
    if ($('#ai_generate_review').is(':checked')) selectedTypes.push('review');
    
    console.log('Tipos seleccionados:', selectedTypes);
    
    if (selectedTypes.length === 0) {
        error_alert('Please select at least one content type to generate.');
        return;
    }
    
    // Deshabilitar botón y mostrar loading
    var $button = $('#generate_ai_content_main');
    var originalText = $button.html();
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating Multiple Content...');
    
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
        timeout: 60000, // 60 segundos para múltiple contenido
        success: function(response) {
            console.log('=== RESPUESTA MÚLTIPLE RECIBIDA ===');
            console.log('Response completo:', response);
            
            $('#loading_modal').modal('hide');
            
            if (response.error) {
                console.error('Error en respuesta:', response.message);
                error_alert('Error generating content: ' + response.message);
                
                // Show debug si está disponible
                if (response.debug) {
                    console.log('Debug info:', response.debug);
                    $('#debug_content').text(JSON.stringify(response.debug, null, 2));
                    $('#debug_modal').modal('show');
                }
            } else {
                console.log('Contenido generado exitosamente para tipos:', Object.keys(response.content));
                
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
                        
                        // Highlight del campo
                        $('#' + fieldId).addClass('highlight-success');
                        setTimeout(function(field) {
                            return function() { $('#' + field).removeClass('highlight-success'); };
                        }(fieldId), 3000);
                        
                        successCount++;
                    }
                }
                
                if (successCount > 0) {
                    success_alert('Successfully generated ' + successCount + ' content types!');
                    
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
            console.log('=== ERROR AJAX MÚLTIPLE ===');
            console.log('Status:', status);
            console.log('Error:', error);
            console.log('Response Text:', xhr.responseText);
            
            $('#loading_modal').modal('hide');
            
            var errorMsg = 'Error generating multiple content: ';
            
            if (status === 'timeout') {
                errorMsg += 'Request timeout. Multiple content generation takes longer.';
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
            $('#debug_content').text('AJAX Error Details:\n' +
                'Status: ' + status + '\n' +
                'Error: ' + error + '\n' +
                'Response: ' + xhr.responseText);
            $('#debug_modal').modal('show');
        },
        complete: function() {
            console.log('=== AJAX MÚLTIPLE COMPLETADO ===');
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

// Función auxiliar para debug de campos
function debugFormFields() {
    console.log('=== DEBUG CAMPOS FORMULARIO ===');
    
    var expectedFields = ['custom_description', 'faq_content', 'howto_content', 'review_content'];
    
    expectedFields.forEach(function(fieldName) {
        var byId = $('#' + fieldName);
        var byName = $('[name="' + fieldName + '"]');
        
        console.log('Campo:', fieldName);
        console.log('  Por ID (#' + fieldName + '):', byId.length, byId.length > 0 ? byId[0] : 'No encontrado');
        console.log('  Por name [name="' + fieldName + '"]:', byName.length, byName.length > 0 ? byName[0] : 'No encontrado');
        console.log('  Valor actual:', byId.length > 0 ? byId.val().substring(0, 50) + '...' : 'N/A');
    });
    
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