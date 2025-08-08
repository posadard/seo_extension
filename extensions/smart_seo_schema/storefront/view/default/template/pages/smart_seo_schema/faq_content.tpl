<?php
if (!empty($faq_data) && $faq_data['enabled']) { ?>
<div id="tab_<?php echo $faq_data['id']; ?>" class="tab-pane">
    <div class="content">
        <h3><?php echo $faq_data['title']; ?></h3>
        <?php 
        // Decode HTML entities first
        $content = html_entity_decode($faq_data['content'], ENT_QUOTES, 'UTF-8');
        
        // Parse FAQ content
        $faq_items = array();
        $lines = explode("\n", trim($content));
        
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
            foreach ($faq_items as $item) { ?>
                <div style="margin-bottom: 20px;">
                    <h4><?php echo $item['question']; ?></h4>
                    <p><?php echo $item['answer']; ?></p>
                </div>
            <?php }
        } else {
            // Fallback: display raw content if parsing fails
            echo $content;
        }
        ?>
    </div>
</div>
<?php } ?>