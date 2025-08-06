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

                <?php echo $this->getHookVar('extension_toolbar_buttons'); ?>
            </div>
        </div>

        <?php include($tpl_common_dir . 'content_buttons.tpl'); ?>
    </div>

    <?php echo $form['form_open']; ?>
    <div class="panel-body panel-body-nopadding tab-content col-xs-12">

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
                            maxlength="200"
                            placeholder="Enter 150-160 character description for optimal SEO..."><?php echo $schema_settings['custom_description'] ?? ''; ?></textarea>
                        <div class="help-block">
                            <span id="char_counter" class="pull-right">0/160 characters</span>
                            <span id="char_status" class="text-muted">Enter 150-160 characters for optimal SEO</span>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <button type="button" id="generate_description_ai" class="btn btn-success btn-block" onclick="generateDescriptionAI()">
                            <i class="fa fa-magic"></i> Generate Description
                        </button>
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
                    </div>
                </div>

            </div>
        </div>

        <?php include('reviews_section.tpl'); ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fa fa-code"></i> Additional Schema Properties
                </h4>
            </div>
            <div class="panel-body">
                
                <div class="form-group">
                    <label class="control-label col-sm-3 col-xs-12" for="others_content">
                        Additional Properties:<br>
                        <span class="help">JSON for extra Schema.org properties</span>
                    </label>
                    <div class="col-sm-6 col-xs-12">
                        <textarea 
                            id="others_content" 
                            name="others_content" 
                            class="form-control large-field" 
                            rows="12" 
                            placeholder='Enter valid JSON for additional properties...'><?php echo $schema_settings['others_content'] ?? ''; ?></textarea>
                        <div class="help-block">
                            <i class="fa fa-info-circle"></i> Weight and dimensions are generated automatically from product data.
                            <br>
                            <a href="https://validator.schema.org/" target="_blank" class="text-primary">
                                <i class="fa fa-external-link"></i> Need help? Test your schema at validator.schema.org
                            </a>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                        <div class="btn-group-vertical btn-block">
                            <button type="button" id="validate_json" class="btn btn-warning" onclick="validateOthersContentJSON()">
                                <i class="fa fa-check-circle"></i> Validate JSON
                            </button>
                        </div>
                        <div id="json_validation_result" class="help-block"></div>
                        <div id="json_save_blocker" class="alert alert-danger" style="display: none; margin-top: 10px;">
                            <i class="fa fa-exclamation-triangle"></i> Cannot save with invalid JSON
                        </div>
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
            <button class="btn btn-primary lock-on-click" id="save_button" onclick="return validateBeforeSave()">
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
                        <div class="input-group">
                            <textarea class="form-control" id="review_text" name="text" rows="6" required></textarea>
                            <span class="input-group-btn" style="vertical-align: top;">
                                <button type="button" id="optimize_review_modal" class="btn btn-warning" 
                                        onclick="optimizeReviewInModal()" 
                                        title="Optimize with AI">
                                    <i class="fa fa-magic"></i><br>Optimize
                                </button>
                            </span>
                        </div>
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
.highlight-optimal {
    border: 2px solid #5bc0de !important;
    background-color: #f0f9ff !important;
    transition: all 0.3s ease;
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
#char_counter.optimal {
    color: #5cb85c;
    font-weight: bold;
}
#char_counter.warning {
    color: #f0ad4e;
}
#char_counter.danger {
    color: #d9534f;
}
#optimize_review_modal {
    height: 80px;
    vertical-align: top;
    font-size: 11px;
    text-align: center;
    white-space: nowrap;
}
.json-invalid {
    pointer-events: none;
    opacity: 0.6;
}
</style>

<script type="text/javascript">

var aiDescriptionSettings = {
    targetMin: 150,
    targetMax: 160,
    maxLength: 200
};

var jsonValidationState = {
    isValid: true,
    lastValidated: ''
};

$(document).ready(function() {
    console.log('=== INICIALIZANDO SMART SEO SCHEMA ===');
    
    checkAIStatus();
    updateCharacterCounter();
    
    $('#custom_description').on('input', updateCharacterCounter);
    $('#others_content').on('input', function() {
        // Reset validation state when content changes
        jsonValidationState.isValid = true;
        $('#json_save_blocker').hide();
        $('#json_validation_result').empty();
        updateSaveButtonState();
    });
    
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

function updateCharacterCounter() {
    var text = $('#custom_description').val();
    var length = text.length;
    var counter = $('#char_counter');
    var status = $('#char_status');
    var textarea = $('#custom_description');
    
    counter.text(length + '/' + aiDescriptionSettings.targetMax + ' characters');
    
    counter.removeClass('optimal warning danger');
    textarea.removeClass('highlight-success highlight-error highlight-optimal');
    
    if (length === 0) {
        status.text('Enter 150-160 characters for optimal SEO').removeClass('text-success text-warning text-danger').addClass('text-muted');
    } else if (length >= aiDescriptionSettings.targetMin && length <= aiDescriptionSettings.targetMax) {
        counter.addClass('optimal');
        textarea.addClass('highlight-optimal');
        status.text('✓ Optimal length for SEO!').removeClass('text-muted text-warning text-danger').addClass('text-success');
    } else if (length < aiDescriptionSettings.targetMin) {
        var needed = aiDescriptionSettings.targetMin - length;
        counter.addClass('warning');
        status.text('Need ' + needed + ' more characters').removeClass('text-muted text-success text-danger').addClass('text-warning');
    } else if (length > aiDescriptionSettings.targetMax && length <= 180) {
        var excess = length - aiDescriptionSettings.targetMax;
        counter.addClass('warning');
        status.text('Consider shortening by ' + excess + ' characters').removeClass('text-muted text-success text-danger').addClass('text-warning');
    } else {
        var excess = length - aiDescriptionSettings.targetMax;
        counter.addClass('danger');
        textarea.addClass('highlight-error');
        status.text('Too long! Remove ' + excess + ' characters').removeClass('text-muted text-success text-warning').addClass('text-danger');
    }
}

function updateSaveButtonState() {
    var saveButton = $('#save_button');
    
    if (!jsonValidationState.isValid) {
        saveButton.addClass('json-invalid');
        saveButton.prop('disabled', true);
    } else {
        saveButton.removeClass('json-invalid');
        saveButton.prop('disabled', false);
    }
}

function validateBeforeSave() {
    var othersContent = $('#others_content').val().trim();
    
    if (othersContent !== '') {
        try {
            JSON.parse(othersContent);
            jsonValidationState.isValid = true;
        } catch (e) {
            jsonValidationState.isValid = false;
            error_alert('Cannot save: Invalid JSON in Additional Properties field. Please fix the JSON syntax first.');
            $('#others_content').addClass('highlight-error');
            $('#json_validation_result').html('<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Invalid JSON: ' + e.message + '</span>');
            $('#json_save_blocker').show();
            updateSaveButtonState();
            return false;
        }
    }
    
    jsonValidationState.isValid = true;
    $('#json_save_blocker').hide();
    updateSaveButtonState();
    return true;
}

function checkAIStatus() {
    var apiKey = '<?php echo addslashes($smart_seo_schema_groq_api_key); ?>';
    
    if (!apiKey || apiKey.length < 10) {
        showAIStatus('warning', 'AI features require a valid Groq API key. Configure it in extension settings.');
        $('.btn-success[id*="generate_"]').prop('disabled', true);
    } else {
        showAIStatus('success', 'API Key configured. AI generation ready.');
    }
}

function showAIStatus(type, message) {
    var alertClass = 'alert-' + (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger'));
    $('#ai_status_alert').removeClass('alert-info alert-success alert-warning alert-danger').addClass(alertClass).show();
    $('#ai_status_message').text(message);
}

function generateDescriptionAI() {
    var $button = $('#generate_description_ai');
    var originalText = $button.html();
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');
    
    $('#loading_message').text('Generating optimized description...');
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
                updateCharacterCounter();
                
                if (response.optimal_length) {
                    $('#custom_description').addClass('highlight-optimal');
                    success_alert('✓ Generated ' + response.length + ' character description (optimal range)');
                } else {
                    $('#custom_description').addClass('highlight-success');
                    success_alert('Description generated (' + response.length + ' chars)');
                }
                
                setTimeout(function() {
                    $('#custom_description').removeClass('highlight-success highlight-optimal');
                }, 5000);
            }
        },
        error: function() {
            $('#loading_modal').modal('hide');
            error_alert('Failed to generate description. Please try again.');
        },
        complete: function() {
            $button.prop('disabled', false).html(originalText);
        }
    });
}

function generateFAQAI() {
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
        error: function() {
            $('#loading_modal').modal('hide');
            error_alert('Failed to generate FAQ. Please try again.');
        },
        complete: function() {
            $button.prop('disabled', false).html(originalText);
        }
    });
}

function generateHowToAI() {
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
        error: function() {
            $('#loading_modal').modal('hide');
            error_alert('Failed to generate HowTo. Please try again.');
        },
        complete: function() {
            $button.prop('disabled', false).html(originalText);
        }
    });
}

function testAIConnection() {
    $('#test_ai_connection').attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Testing...');
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/testAIConnection", "&product_id=" . $product_id); ?>',
        type: 'GET',
        dataType: 'json',
        timeout: 15000,
        success: function(response) {
            if (response.error) {
                showAIStatus('danger', response.message);
                error_alert(response.message);
            } else {
                showAIStatus('success', response.message);
                success_alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            var errorMsg = 'Connection test failed: ';
            if (status === 'timeout') {
                errorMsg += 'Request timeout';
            } else {
                errorMsg += error;
            }
            
            showAIStatus('danger', errorMsg);
            error_alert(errorMsg);
        },
        complete: function() {
            $('#test_ai_connection').attr('disabled', false).html('<i class="fa fa-flask fa-lg"></i> <?php echo $button_test_ai_connection; ?>');
        }
    });
}

function previewSchema() {
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
                $('#variants_list').html('<p class="text-muted"><i class="fa fa-info-circle"></i> No variants found.</p>');
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
        resultDiv.html('<span class="text-info"><i class="fa fa-info-circle"></i> JSON field is empty.</span>');
        jsonValidationState.isValid = true;
        $('#json_save_blocker').hide();
        updateSaveButtonState();
        return;
    }
    
    try {
        var parsed = JSON.parse(jsonText);
        resultDiv.html('<span class="text-success"><i class="fa fa-check-circle"></i> Valid JSON format!</span>');
        $('#others_content').removeClass('highlight-error').addClass('highlight-success');
        
        jsonValidationState.isValid = true;
        jsonValidationState.lastValidated = jsonText;
        $('#json_save_blocker').hide();
        updateSaveButtonState();
        
        setTimeout(function() {
            $('#others_content').removeClass('highlight-success');
        }, 3000);
        
    } catch (e) {
        resultDiv.html('<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Invalid JSON: ' + e.message + '</span>');
        $('#others_content').removeClass('highlight-success').addClass('highlight-error');
        
        jsonValidationState.isValid = false;
        $('#json_save_blocker').show();
        updateSaveButtonState();
        
        setTimeout(function() {
            $('#others_content').removeClass('highlight-error');
        }, 5000);
    }
}

function optimizeReviewInModal() {
    var currentText = $('#review_text').val().trim();
    
    if (!currentText) {
        error_alert('Please enter review text first.');
        return;
    }
    
    var $button = $('#optimize_review_modal');
    var originalText = $button.html();
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i><br>Working...');
    
    $.ajax({
        url: '<?php echo $this->html->getSecureURL("catalog/smart_seo_schema/optimizeReview", "&product_id=" . $product_id); ?>',
        type: 'POST',
        data: {
            review_id: $('#review_id').val() || 'new',
            review_text: currentText
        },
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            if (response.error) {
                error_alert('Error optimizing review: ' + response.message);
            } else {
                $('#review_text').val(response.optimized_text);
                $('#review_text').addClass('highlight-success');
                setTimeout(function() {
                    $('#review_text').removeClass('highlight-success');
                }, 3000);
                success_alert('Review optimized successfully!');
            }
        },
        error: function() {
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

</script>