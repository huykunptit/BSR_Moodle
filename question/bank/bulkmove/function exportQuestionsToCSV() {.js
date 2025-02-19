function exportQuestionsToCSV() {
    // Select all question blocks
    const questionBlocks = document.querySelectorAll(\'.questionblock\');
    
    // Array to hold the CSV rows
    const csvRows = [];
    
    // Add header row
    csvRows.push([\'no\', \'questionname\', \'questiontext\']);
    
    // Iterate through each question block
    questionBlocks.forEach((block, index) => {
        const questionName = block.querySelector(\'.questionname\').textContent.trim();
        const questionText = block.querySelector(\'.questiontext\').textContent.trim();
        const questionContent = block.querySelector(\'p:nth-of-type(2)\').textContent.trim();
        
        // Enclose content in double quotes to handle commas
        const escapedQuestionContent = `"${questionContent.replace(/"/g, \'""\')}"`;
        
        // Add the question number and content to the CSV rows
        csvRows.push([index + 1, questionName, escapedQuestionContent]);
    });
    
    // Convert the rows to CSV format
    const csvContent = csvRows.map(row => row.join(\',\')).join(\'\n\');
    
    // Prepend BOM for UTF-8 encoding
    const bom = \'\uFEFF\';
    const csvWithBom = bom + csvContent;
    
    // Create a Blob from the CSV content with BOM
    const blob = new Blob([csvWithBom], { type: \'text/csv;charset=utf-8;\' });
    
    // Create a link element
    const a = document.createElement(\'a\');
    
    // Create a URL for the Blob and set it as the href attribute
    const url = URL.createObjectURL(blob);
    a.href = url;
    
    // Set the download attribute to the desired file name
    a.download = \'questions.csv\';
    
    // Append the link to the document body
    document.body.appendChild(a);
    
    // Programmatically click the link to trigger the download
    a.click();
    
    // Remove the link from the document
    document.body.removeChild(a);
}