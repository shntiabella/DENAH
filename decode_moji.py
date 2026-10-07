import re
p = r'D:\project_kp\resources\views\livewire\public\peta.blade.php'
s = open(p, encoding='utf-8').read()

opts = re.findall(r'<option value=\"\">[^<]*</option>', s)
print('=== OPTION placeholders found:', len(opts), '===')
for o in opts:
    print('OPT:', repr(o[:120]))

def unmoj(t):
    try:
        return t.encode('latin-1').decode('utf-8')
    except Exception as e:
        return 'ERR ' + str(e)

print('\n=== reverse-decode each option inner text ===')
for o in opts:
    inner = re.search(r'<option value=\"\">(.*)</option>', o)
    if inner:
        raw = inner.group(1)
        print('RAW   :', repr(raw[:140]))
        print('FIXED :', repr(unmoj(raw)[:140]))
        print('---')

print('\n=== raster loading strings (Memuat raster...) ===')
for m in re.finditer(r'Memuat[^<]*', s):
    raw = m.group(0)
    print('RAW   :', repr(raw[:140]))
    print('FIXED :', repr(unmoj(raw)[:140]))
    print('---')
