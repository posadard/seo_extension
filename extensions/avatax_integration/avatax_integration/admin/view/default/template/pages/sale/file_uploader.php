<!-- Simple Form for inserting data into the table -->
<form action="extensions/avatax_integration/admin/view/default/template/pages/sale/insert_customer_file.php" method="post" enctype="multipart/form-data" style="max-width: 400px; margin: auto; padding: 10px; border: 1px solid #ccc; border-radius: 8px; background-color: #f9f9f9; font-family: monospace;">
    <h3 style="text-align: center; margin-bottom: 5px; font-size: 16px;">UPLOAD CUSTOMERS DOCUMENT</h3>
    
    <div style="margin-bottom: 5px;">
        <label for="file" style="display: block; margin-bottom: 2px;">File:</label>
        <input type="file" id="file" name="file" required style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    
    <div style="margin-bottom: 5px;">
        <label for="preset" style="display: block; margin-bottom: 2px;">Document Type:</label>
        <select id="preset" name="preset" required onchange="showOtherField()" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="DHS">DHS</option>
            <option value="TAX">TAX</option>
            <option value="Other">Other</option>
        </select>
    </div>
    
    <div id="otherFieldContainer" style="margin-bottom: 5px; display: none;">
        <label for="other" style="display: block; margin-bottom: 2px;">Other Document Type:</label>
        <input type="text" id="other" name="other" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    
    <input type="hidden" id="customer_id" name="customer_id" value="<?php echo isset($_GET['customer_id']) ? htmlspecialchars($_GET['customer_id']) : ''; ?>" required>
    
    <div style="text-align: center;">
        <input type="submit" value="Submit" style="background-color: #4CAF50; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer;">
    </div>
</form>

<script>
function showOtherField() {
    var preset = document.getElementById("preset").value;
    var otherFieldContainer = document.getElementById("otherFieldContainer");
    otherFieldContainer.style.display = (preset === "Other") ? "block" : "none";
}
</script>
