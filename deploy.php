<?php
namespace Deployer;

/* | To run this task install deployer first globally:
 * | $ composer global require deployer/deployer
 * |
 * | Then you can call `dep upload `
 */

argument('stage', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Run tasks only on this server or group of servers');

set('exclude_from_upload', [
    '.git',
    '.idea',
    '.vagrant',
    'vendor',
    'Documentation'
]);


server('vagrant', '192.168.0.100')
    ->user('vagrant')
    ->password('vagrant')
    ->set('deploy_path', '/var/www/html/typo3conf/ext/dce');


desc('Uploads local project files.');
task('upload', function (){
    $directory = new \RecursiveDirectoryIterator(getcwd(), \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::SKIP_DOTS);
    $files = new \RecursiveIteratorIterator($directory);
    /** @var \SplFileInfo $file */
    foreach ($files as $file) {
        $relativeFilePath = substr($file->getPathname(), strlen(getcwd()) + 1);
        $relativeFilePathParts = explode(DIRECTORY_SEPARATOR, $relativeFilePath);

        if (in_array($file->getBasename(), get('exclude_from_upload')) ||
            in_array(reset($relativeFilePathParts), get('exclude_from_upload'))
        ) {
            continue;
        }
        upload($file->getPathname(), get('deploy_path') . '/' . $relativeFilePath);
    }
    writeln('Done.');
});
