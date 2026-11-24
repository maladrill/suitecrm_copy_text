<?php
// Check the PHP files shipped in the ZIP against SuiteCRM ModuleScanner restrictions.
$paths = array('manifest.php');
foreach (array('scripts', 'src') as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $paths[] = $file->getPathname();
        }
    }
}

$blocked = array_flip(array(
    'file_exists', 'file_get_contents', 'file_put_contents', 'file', 'fopen',
    'fclose', 'fwrite', 'glob', 'is_dir', 'is_file', 'is_readable', 'is_writable',
    'mkdir', 'rmdir', 'unlink', 'copy', 'rename', 'move_uploaded_file', 'tempnam',
    'chmod', 'realpath', 'pathinfo', 'exec', 'system', 'shell_exec', 'eval',
    'call_user_func', 'call_user_func_array', 'array_map', 'array_filter',
    'array_reduce', 'array_walk', 'usort', 'preg_replace_callback', 'ob_start',
    'simplexml_load_string',
));
$failed = false;
foreach ($paths as $path) {
    $tokens = token_get_all(file_get_contents($path));
    foreach ($tokens as $index => $token) {
        if (!is_array($token)) {
            if ($token === '`') {
                fwrite(STDERR, "$path: shell backtick is blocked\n");
                $failed = true;
            }
            continue;
        }
        if (in_array($token[0], array(T_EXIT, T_ECHO, T_EVAL), true)) {
            fwrite(STDERR, "$path: exit/die, echo or eval is blocked\n");
            $failed = true;
        }
        if ($token[0] !== T_STRING || !isset($blocked[strtolower($token[1])])) {
            continue;
        }
        $next = $index + 1;
        while (isset($tokens[$next]) && is_array($tokens[$next])
            && $tokens[$next][0] === T_WHITESPACE) {
            $next++;
        }
        if (isset($tokens[$next]) && $tokens[$next] === '(') {
            fwrite(STDERR, "$path: blocked function {$token[1]}()\n");
            $failed = true;
        }
    }
}
if ($failed) {
    throw new RuntimeException('ModuleScanner compatibility check failed');
}
