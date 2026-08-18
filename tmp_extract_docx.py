import zipfile
import xml.etree.ElementTree as ET
from pathlib import Path

path = Path('Rapport de stage.docx')
if not path.exists():
    raise SystemExit('DOCX file not found')

with zipfile.ZipFile(path, 'r') as z:
    xml = z.read('word/document.xml')
root = ET.fromstring(xml)
ns = {'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'}
texts = []
for para in root.findall('.//w:p', ns):
    text_nodes = [node.text for node in para.findall('.//w:t', ns) if node.text]
    if text_nodes:
        texts.append(''.join(text_nodes))
print('\n'.join(texts))
