<?php if (!empty($faq_content)) { ?>
<div class="faq-tab-content">
    <div class="faq-header">
        <h3><?php echo $text_faq_title; ?></h3>
        <p class="faq-description"><?php echo $text_faq_description; ?></p>
    </div>
    
    <div class="faq-list">
        <?php 
        // Parse FAQ content - handle both AI-generated and manual formats
        $faq_items = array();
        $lines = explode("\n", trim($faq_content));
        
        $current_question = '';
        $current_answer = '';
        $in_answer = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Detect question (starts with Q: or ends with ?)
            if (preg_match('/^Q:\s*(.+)/', $line, $matches)) {
                // Save previous Q&A if exists
                if ($current_question && $current_answer) {
                    $faq_items[] = array(
                        'question' => $current_question,
                        'answer' => trim($current_answer)
                    );
                }
                $current_question = trim($matches[1]);
                $current_answer = '';
                $in_answer = false;
            } elseif (preg_match('/^A:\s*(.+)/', $line, $matches)) {
                $current_answer = trim($matches[1]);
                $in_answer = true;
            } elseif (substr($line, -1) === '?' && !$in_answer) {
                // Save previous Q&A if exists
                if ($current_question && $current_answer) {
                    $faq_items[] = array(
                        'question' => $current_question,
                        'answer' => trim($current_answer)
                    );
                }
                $current_question = $line;
                $current_answer = '';
                $in_answer = false;
            } else {
                // Continue answer or start answer if we have a question
                if ($current_question) {
                    if (!empty($current_answer)) {
                        $current_answer .= ' ' . $line;
                    } else {
                        $current_answer = $line;
                    }
                    $in_answer = true;
                }
            }
        }
        
        // Add final Q&A pair
        if ($current_question && $current_answer) {
            $faq_items[] = array(
                'question' => $current_question,
                'answer' => trim($current_answer)
            );
        }
        
        if (!empty($faq_items)) {
            $item_count = 0;
            foreach ($faq_items as $item) {
                $item_count++;
        ?>
        <div class="faq-item" id="faq-item-<?php echo $item_count; ?>">
            <div class="faq-question" onclick="toggleFAQ(<?php echo $item_count; ?>)">
                <h4>
                    <span class="faq-icon">
                        <i class="fa fa-plus" id="faq-icon-<?php echo $item_count; ?>"></i>
                    </span>
                    <?php echo htmlspecialchars($item['question'], ENT_QUOTES, 'UTF-8'); ?>
                </h4>
            </div>
            <div class="faq-answer" id="faq-answer-<?php echo $item_count; ?>" style="display: none;">
                <div class="faq-answer-content">
                    <?php echo nl2br(htmlspecialchars($item['answer'], ENT_QUOTES, 'UTF-8')); ?>
                </div>
            </div>
        </div>
        <?php 
            }
        } else {
            // Fallback: display raw content if parsing fails
        ?>
        <div class="faq-raw-content">
            <?php echo nl2br(htmlspecialchars($faq_content, ENT_QUOTES, 'UTF-8')); ?>
        </div>
        <?php } ?>
    </div>
</div>

<style>
.faq-tab-content {
    padding: 20px;
    max-width: 100%;
}

.faq-header {
    margin-bottom: 25px;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 15px;
}

.faq-header h3 {
    color: #333;
    font-size: 24px;
    margin: 0 0 10px 0;
    font-weight: 600;
}

.faq-description {
    color: #666;
    font-size: 14px;
    margin: 0;
    line-height: 1.5;
}

.faq-list {
    margin-top: 20px;
}

.faq-item {
    border: 1px solid #e8e8e8;
    border-radius: 8px;
    margin-bottom: 12px;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.faq-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.faq-question {
    padding: 18px 20px;
    cursor: pointer;
    border-radius: 8px;
    transition: background-color 0.3s ease;
    user-select: none;
}

.faq-question:hover {
    background-color: #f8f9fa;
}

.faq-question h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 500;
    color: #333;
    display: flex;
    align-items: center;
    line-height: 1.4;
}

.faq-icon {
    margin-right: 12px;
    color: #007bff;
    font-size: 14px;
    min-width: 20px;
    transition: transform 0.3s ease;
}

.faq-answer {
    border-top: 1px solid #f0f0f0;
    animation: slideDown 0.3s ease;
}

.faq-answer-content {
    padding: 20px;
    color: #555;
    line-height: 1.6;
    font-size: 15px;
}

.faq-raw-content {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    color: #555;
    line-height: 1.6;
    border-left: 4px solid #007bff;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
    }
    to {
        opacity: 1;
        max-height: 500px;
    }
}

/* Responsive design */
@media (max-width: 768px) {
    .faq-tab-content {
        padding: 15px;
    }
    
    .faq-question {
        padding: 15px;
    }
    
    .faq-answer-content {
        padding: 15px;
    }
    
    .faq-header h3 {
        font-size: 20px;
    }
    
    .faq-question h4 {
        font-size: 15px;
    }
}

@media (max-width: 480px) {
    .faq-tab-content {
        padding: 10px;
    }
    
    .faq-question {
        padding: 12px;
    }
    
    .faq-answer-content {
        padding: 12px;
    }
}
</style>

<script>
function toggleFAQ(itemId) {
    var answer = document.getElementById('faq-answer-' + itemId);
    var icon = document.getElementById('faq-icon-' + itemId);
    
    if (answer.style.display === 'none' || answer.style.display === '') {
        answer.style.display = 'block';
        icon.className = 'fa fa-minus';
        
        // Close other open FAQ items (optional accordion behavior)
        var allAnswers = document.querySelectorAll('.faq-answer');
        var allIcons = document.querySelectorAll('.faq-icon i');
        
        allAnswers.forEach(function(item, index) {
            if (item.id !== 'faq-answer-' + itemId && item.style.display === 'block') {
                item.style.display = 'none';
                allIcons[index].className = 'fa fa-plus';
            }
        });
    } else {
        answer.style.display = 'none';
        icon.className = 'fa fa-plus';
    }
}

// Auto-expand first FAQ item on load (optional)
document.addEventListener('DOMContentLoaded', function() {
    var firstAnswer = document.getElementById('faq-answer-1');
    var firstIcon = document.getElementById('faq-icon-1');
    
    if (firstAnswer && firstIcon) {
        // Uncomment to auto-expand first item
        // firstAnswer.style.display = 'block';
        // firstIcon.className = 'fa fa-minus';
    }
});
</script>

<?php } else { ?>
<div class="faq-tab-content">
    <div class="faq-empty">
        <div class="faq-empty-icon">
            <i class="fa fa-question-circle"></i>
        </div>
        <h3><?php echo $text_faq_no_content_title; ?></h3>
        <p><?php echo $text_faq_no_content_message; ?></p>
    </div>
</div>

<style>
.faq-empty {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.faq-empty-icon {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 20px;
}

.faq-empty h3 {
    color: #333;
    margin-bottom: 10px;
    font-size: 18px;
}

.faq-empty p {
    color: #999;
    font-size: 14px;
    margin: 0;
}
</style>
<?php } ?>