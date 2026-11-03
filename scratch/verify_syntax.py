import sys

file_path = sys.argv[1] if len(sys.argv) > 1 else 'models/m_kary/m_kary-custom.php'
with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
    code = f.read()

paren = 0
bracket = 0
curly = 0
in_string = False
string_char = ''
in_comment_single = False
in_comment_multi = False

i = 0
n = len(code)
while i < n:
    c = code[i]
    next_c = code[i+1] if i + 1 < n else ''

    if in_comment_single:
        if c == '\n':
            in_comment_single = False
        i += 1
        continue

    if in_comment_multi:
        if c == '*' and next_c == '/':
            in_comment_multi = False
            i += 2
            continue
        i += 1
        continue

    if in_string:
        if c == '\\':
            i += 2
            continue
        if c == string_char:
            in_string = False
        i += 1
        continue

    # Not in string or comment
    if c == '/' and next_c == '/':
        in_comment_single = True
        i += 2
        continue
    if c == '#' and not (next_c == '['): # PHP 8 attribute might use #[
        in_comment_single = True
        i += 1
        continue
    if c == '/' and next_c == '*':
        in_comment_multi = True
        i += 2
        continue

    if c in ("'", '"'):
        in_string = True
        string_char = c
        i += 1
        continue

    if c == '(': paren += 1
    elif c == ')': paren -= 1
    elif c == '[': bracket += 1
    elif c == ']': bracket -= 1
    elif c == '{': curly += 1
    elif c == '}': curly -= 1

    i += 1

print(f"Results for {file_path}:")
print(f"Paren balance: {paren}")
print(f"Bracket balance: {bracket}")
print(f"Curly balance: {curly}")
if paren == 0 and bracket == 0 and curly == 0:
    print("Syntax balance OK!")
else:
    print("WARNING: Syntax balance mismatch!")

