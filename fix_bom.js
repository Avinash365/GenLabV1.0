const fs = require('fs');
const path = require('path');

const files = [
    'app/Http/Controllers/Api/WhatsappWebhookController.php',
    'routes/api.php'
];

files.forEach(relativePath => {
    const filePath = path.resolve(__dirname, relativePath);
    try {
        if (fs.existsSync(filePath)) {
            const buffer = fs.readFileSync(filePath);
            
            // BOM signature for UTF-8 is 0xEF 0xBB 0xBF
            if (buffer.length >= 3 && buffer[0] === 0xEF && buffer[1] === 0xBB && buffer[2] === 0xBF) {
                console.log(`BOM detect in ${relativePath}. Removing...`);
                const contentWithoutBOM = buffer.slice(3);
                fs.writeFileSync(filePath, contentWithoutBOM);
                console.log(`Fixed ${relativePath}`);
            } else {
                console.log(`No BOM needed for ${relativePath}`);
            }
        } else {
            console.warn(`File not found: ${filePath}`);
        }
    } catch (err) {
        console.error(`Error processing ${filePath}: ${err.message}`);
    }
});