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

set('bin/rm', 'rm -Rf ');

server('vagrant', '192.168.0.100')
    ->user('vagrant')
    ->password('vagrant')
    ->set('deploy_path', '/var/www/html/typo3conf/ext/dce');

desc('If running it watches files (respecting "exclude_from_upload" config) and uploads or deletes them on remote.');
task('watch', function(){
    // /!\ Info
    // /!\ Requires installed composer package: "jasonlewis/resource-watcher" before run deployer (`dep watch`)
    $files = new \Illuminate\Filesystem\Filesystem;
    $tracker = new \JasonLewis\ResourceWatcher\Tracker;

    $watcher = new \JasonLewis\ResourceWatcher\Watcher($tracker, $files);
    $listener = $watcher->watch(getcwd());

    $listener->modify(function($resource, $path) {
        $relativePath = substr($path, strlen(getcwd()) + 1);
        if (isValidFile($path)) {
            upload($path, get('deploy_path') . '/' . $relativePath);
        }
    });
    $listener->create(function($resource, $path) {
        $relativePath = substr($path, strlen(getcwd()) + 1);
        if (isValidFile($path)) {
            upload($path, get('deploy_path') . '/' . $relativePath);
        }
    });
    $listener->delete(function($resource, $path) {
        $relativePath = substr($path, strlen(getcwd()) + 1);
        if (isValidFile($path)) {
            writeln('Delete file ' . get('deploy_path') . '/' . $relativePath);
            run(get('bin/rm') . get('deploy_path') . '/' . $relativePath);
        }
    });
    $watcher->start();
});

desc('Full uploads of local project files (without excluded files).');
task('upload', function (){
    $directory = new \RecursiveDirectoryIterator(getcwd(), \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::SKIP_DOTS);
    $files = new \RecursiveIteratorIterator($directory);
    /** @var \SplFileInfo $file */
    foreach ($files as $file) {
        $relativeFilePath = substr($file->getPathname(), strlen(getcwd()) + 1);
        if (!isValidFile($file->getPathname())) {
            continue;
        }
        upload($file->getPathname(), get('deploy_path') . '/' . $relativeFilePath);
    }
    writeln('Done.');
});

/**
 * Checks if given file path is not excluded by "exclude_from_upload"
 * configuration parameter
 *
 * @param string $path File path
 * @return bool If true the file is not excluded and can proceed. On false the file is excluded.
 */
function isValidFile($path)
{
    $relativeFilePath = substr($path, strlen(getcwd()) + 1);
    $relativeFilePathParts = explode(DIRECTORY_SEPARATOR, $relativeFilePath);

    if (in_array(basename($path), get('exclude_from_upload')) ||
        in_array(reset($relativeFilePathParts), get('exclude_from_upload'))
    ) {
        return false;
    }
    return true;
}
