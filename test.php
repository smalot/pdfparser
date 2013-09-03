<?php

$content = '
1 0 obj
<</Length 6 0 R>>
stream
foo
endstream
endobj
2 0 obj
<</Length 6 0 R>>
stream
foo
endstream
3 0 obj
<</Length 6 0 R>>
stream
foo
endstream
endobj
invalid section';

var_dump(preg_split('/([\n\r]{1,2}[0-9]+\s+[0-9]+\s+obj)/s', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE));