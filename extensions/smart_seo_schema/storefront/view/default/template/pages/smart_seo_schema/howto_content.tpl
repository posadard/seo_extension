<?php
if (!empty($howto_data) && $howto_data['enabled']) { ?>
<div id="tab_<?php echo $howto_data['id']; ?>" class="tab-pane">
    <div class="content">
        <h3><?php echo $howto_data['title']; ?></h3>
        <?php 
        // Decode HTML entities first
        $content = html_entity_decode($howto_data['content'], ENT_QUOTES, 'UTF-8');
        
        // Parse HowTo content
        $steps = array();
        $lines = explode("\n", trim($content));
        
        $current_step = '';
        $step_number = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Detect step format: "Step 1:", "1.", "Step 1 -", etc.
            if (preg_match('/^(?:\*\*)?(?:Step\s*)?(\d+)[\.\:\-\s]+(.+)/i', $line, $matches)) {
                if (!empty($current_step)) {
                    $steps[] = array(
                        'number' => $step_number,
                        'content' => trim($current_step)
                    );
                }
                $step_number = (int)$matches[1];
                // Remove any markdown bold markers
                $current_step = trim(str_replace('**', '', $matches[2]));
            } else {
                // Continue previous step content
                if (!empty($current_step)) {
                    $current_step .= ' ' . str_replace('**', '', $line);
                } else {
                    // If no step format found, create sequential steps
                    $step_number++;
                    $current_step = str_replace('**', '', $line);
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
        
        if (!empty($steps)) { ?>
            <ol>
                <?php foreach ($steps as $step) { ?>
                    <li>
                        <strong>Step <?php echo $step['number']; ?>:</strong>
                        <?php echo $step['content']; ?>
                    </li>
                <?php } ?>
            </ol>
        <?php } else {
            // Fallback: display raw content if parsing fails
            echo str_replace('**', '', $content);
        }
        ?>
    </div>
</div>
<?php } ?>