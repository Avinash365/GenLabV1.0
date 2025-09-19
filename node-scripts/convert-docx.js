#!/usr/bin/env node
/**
 * Simple DOCX -> HTML converter using mammoth.
 * Usage: node convert-docx.js /absolute/path/to/file.docx
 * Writes HTML to stdout. Exit code 0 on success, 1 on failure.
 */
const fs = require('fs');
const path = require('path');
const mammoth = require('mammoth');

(async () => {
  const file = process.argv[2];
  if(!file){
    console.error('No file provided');
    process.exit(1);
  }
  try {
    const result = await mammoth.convertToHtml({ path: file }, {
      styleMap: [
        'p[style-name="Title"] => h1:fresh',
        'p[style-name="Subtitle"] => h2:fresh'
      ]
    });
    const html = result.value || '';
    process.stdout.write(html.trim());
    process.exit(0);
  } catch(err){
    console.error('Conversion error:', err.message);
    process.exit(1);
  }
})();
