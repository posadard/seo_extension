<?php if (!empty($howto_content)) { ?>
<div class="howto-tab-content">
    <div class="howto-header">
        <h3><?php echo $text_howto_title; ?></h3>
        <p class="howto-description"><?php echo $text_howto_description; ?></p>
    </div>
    
    <div class="howto-steps">
        <?php 
        // Parse HowTo content - handle both AI-generated and manual formats
        $steps = array();
        $lines = explode("\n", trim($howto_content));
        
        $current_step = '';
        $step_number = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Detect step format: "Step 1:", "1.", "Step 1 -", etc.
            if (preg_match('/^(?:Step\s*)?(\d+)[\.\:\-\s]+(.+)/i', $line, $matches)) {
                if (!empty($current_step)) {
                    $steps[] = array(
                        'number' => $step_number,
                        'content' => trim($current_step)
                    );
                }
                $step_number = (int)$matches[1];
                $current_step = trim($matches[2]);
            } else {
                // Continue previous step content
                if (!empty($current_step)) {
                    $current_step .= ' ' . $line;
                } else {
                    // If no step format found, create sequential steps
                    $step_number++;
                    $current_step = $line;
                }
            }
        }
        
        // Add final step
        if (!empty($current_step)) {
            $steps[] = array(
                'number' => $step_number,
                'content' => trim($current_step)
            );
        }
        
        if (!empty($steps)) {
        ?>
        <div class="steps-container">
            <?php foreach ($steps as $index => $step) { ?>
            <div class="step-item" data-step="<?php echo $step['number']; ?>">
                <div class="step-number">
                    <span class="step-circle">
                        <?php echo $step['number']; ?>
                    </span>
                    <?php if ($index < count($steps) - 1) { ?>
                    <div class="step-line"></div>
                    <?php } ?>
                </div>
                <div class="step-content">
                    <div class="step-text">
                        <?php echo nl2br(htmlspecialchars($step['content'], ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                    <?php if ($index < count($steps) - 1) { ?>
                    <div class="step-arrow">
                        <i class="fa fa-arrow-down"></i>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
        
        <div class="howto-footer">
            <div class="howto-completion">
                <i class="fa fa-check-circle"></i>
                <span><?php echo $text_howto_completion; ?></span>
            </div>
        </div>
        
        <?php } else { ?>
        <!-- Fallback: display raw content if parsing fails -->
        <div class="howto-raw-content">
            <div class="content-wrapper">
                <?php echo nl2br(htmlspecialchars($howto_content, ENT_QUOTES, 'UTF-8')); ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<style>
.howto-tab-content {
    padding: 20px;
    max-width: 100%;
}

.howto-header {
    margin-bottom: 30px;
    text-align: center;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 20px;
}

.howto-header h3 {
    color: #333;
    font-size: 24px;
    margin: 0 0 10px 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}

.howto-header h3::before {
    content: "\f0c9";
    font-family: FontAwesome;
    margin-right: 10px;
    color: #28a745;
}

.howto-description {
    color: #666;
    font-size: 14px;
    margin: 0;
    line-height: 1.5;
}

.steps-container {
    margin: 30px 0;
}

.step-item {
    display: flex;
    margin-bottom: 25px;
    position: relative;
    animation: fadeInUp 0.6s ease forwards;
    opacity: 0;
}

.step-item:nth-child(1) { animation-delay: 0.1s; }
.step-item:nth-child(2) { animation-delay: 0.2s; }
.step-item:nth-child(3) { animation-delay: 0.3s; }
.step-item:nth-child(4) { animation-delay: 0.4s; }
.step-item:nth-child(5) { animation-delay: 0.5s; }
.step-item:nth-child(n+6) { animation-delay: 0.6s; }

.step-number {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-right: 20px;
    position: relative;
    min-width: 50px;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    z-index: 2;
    position: relative;
}

.step-line {
    width: 2px;
    height: 40px;
    background: linear-gradient(to bottom, #28a745, #e9ecef);
    margin-top: 10px;
    position: relative;
}

.step-content {
    flex: 1;
    background: #fff;
    padding: 20px 25px;
    border-radius: 12px;
    border: 1px solid #e8e8e8;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    position: relative;
}

.step-content:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.step-content::before {
    content: '';
    position: absolute;
    left: -10px;
    top: 20px;
    width: 0;
    height: 0;
    border-top: 10px solid transparent;
    border-bottom: 10px solid transparent;
    border-right: 10px solid #fff;
    z-index: 2;
}

.step-content::after {
    content: '';
    position: absolute;
    left: -12px;
    top: 19px;
    width: 0;
    height: 0;
    border-top: 11px solid transparent;
    border-bottom: 11px solid transparent;
    border-right: 11px solid #e8e8e8;
    z-index: 1;
}

.step-text {
    color: #333;
    line-height: 1.6;
    font-size: 15px;
    margin: 0;
}

.step-arrow {
    text-align: center;
    margin: 15px 0 0 0;
    color: #28a745;
    font-size: 18px;
    opacity: 0.7;
}

.howto-footer {
    margin-top: 40px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 12px;
    border-left: 4px solid #28a745;
}

.howto-completion {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #28a745;
    font-weight: 500;
    font-size: 16px;
}

.howto-completion i {
    font-size: 20px;
    margin-right: 10px;
}

.howto-raw-content {
    margin: 20px 0;
}

.content-wrapper {
    padding: 25px;
    background: #f8f9fa;
    border-radius: 12px;
    border-left: 4px solid #28a745;
    color: #555;
    line-height: 1.7;
    font-size: 15px;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Progress indicator (optional enhancement) */
.progress-indicator {
    position: fixed;
    top: 50%;
    right: 30px;
    transform: translateY(-50%);
    display: none;
}

.progress-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ddd;
    margin: 8px 0;
    transition: background 0.3s ease;
}

.progress-dot.active {
    background: #28a745;
}

/* Responsive design */
@media (max-width: 768px) {
    .howto-tab-content {
        padding: 15px;
    }
    
    .step-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .step-number {
        flex-direction: row;
        margin-right: 0;
        margin-bottom: 15px;
        width: 100%;
        justify-content: flex-start;
    }
    
    .step-circle {
        margin-right: 15px;
    }
    
    .step-line {
        height: 2px;
        width: 50px;
        margin-top: 0;
        margin-left: 10px;
        background: linear-gradient(to right, #28a745, #e9ecef);
    }
    
    .step-content {
        margin-left: 0;
        width: 100%;
    }
    
    .step-content::before,
    .step-content::after {
        display: none;
    }
    
    .howto-header h3 {
        font-size: 20px;
    }
    
    .step-text {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .howto-tab-content {
        padding: 10px;
    }
    
    .step-content {
        padding: 15px 18px;
    }
    
    .content-wrapper {
        padding: 18px;
    }
    
    .step-circle {
        width: 35px;
        height: 35px;
        font-size: 14px;
    }
}

/* Print styles */
@media print {
    .step-content {
        box-shadow: none;
        border: 1px solid #ccc;
    }
    
    .step-arrow {
        display: none;
    }
}
</style>

<script>
// Optional: Add step completion tracking
document.addEventListener('DOMContentLoaded', function() {
    var steps = document.querySelectorAll('.step-item');
    
    // Add click handlers for step completion (optional)
    steps.forEach(function(step, index) {
        step.addEventListener('click', function() {
            step.classList.toggle('completed');
            var circle = step.querySelector('.step-circle');
            if (step.classList.contains('completed')) {
                circle.innerHTML = '<i class="fa fa-check"></i>';
                circle.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
            } else {
                circle.innerHTML = index + 1;
                circle.style.background = 'linear-gradient(135deg, #6c757d, #495057)';
            }
        });
    });
    
    // Optional: Smooth scroll between steps
    var stepNumbers = document.querySelectorAll('.step-circle');
    stepNumbers.forEach(function(circle, index) {
        circle.style.cursor = 'pointer';
        circle.addEventListener('click', function(e) {
            e.stopPropagation();
            var targetStep = document.querySelector('.step-item[data-step="' + (index + 1) + '"]');
            if (targetStep) {
                targetStep.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });
    });
});
</script>

<?php } else { ?>
<div class="howto-tab-content">
    <div class="howto-empty">
        <div class="howto-empty-icon">
            <i class="fa fa-list-ol"></i>
        </div>
        <h3><?php echo $text_howto_no_content_title; ?></h3>
        <p><?php echo $text_howto_no_content_message; ?></p>
    </div>
</div>

<style>
.howto-empty {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.howto-empty-icon {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 20px;
}

.howto-empty h3 {
    color: #333;
    margin-bottom: 10px;
    font-size: 18px;
}

.howto-empty p {
    color: #999;
    font-size: 14px;
    margin: 0;
}
</style>
<?php } ?>