file_path = r'models/t_resign/custom.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

target = """    public function updateAfter($model, $arrayData, $metaData, $id = null)"""

replacement = """    public function createAfter($model, $arrayData, $metaData, $id = null)
    {
        $this->updateAfter($model, $arrayData, $metaData, $id);
    }

    public function updateAfter($model, $arrayData, $metaData, $id = null)"""

norm_content = content.replace('\r\n', '\n')
norm_target = target.replace('\r\n', '\n')
norm_replacement = replacement.replace('\r\n', '\n')

if norm_target in norm_content:
    new_content = norm_content.replace(norm_target, norm_replacement, 1)
    new_content = new_content.replace('\n', '\r\n')
    with open(file_path, 'wb') as f:
        f.write(new_content.encode('utf-8'))
    print("Successfully added createAfter to t_resign/custom.php")
else:
    print("Target not found")

