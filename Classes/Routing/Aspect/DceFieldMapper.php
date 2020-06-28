<?php declare(strict_types=1);
namespace T3\Dce\Routing\Aspect;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;
use TYPO3\CMS\Core\Site\SiteLanguageAwareInterface;

class DceFieldMapper implements StaticMappableAspectInterface, SiteLanguageAwareInterface
{
    use \TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;

    private $settings;

    /**
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function generate(string $value): ?string
    {
        /** @var \T3\Dce\Domain\Repository\DceRepository $repo */
        $repo = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\T3\Dce\Domain\Repository\DceRepository::class);
        $dce = $repo->getDceInstance((int) $value);

        $field = $dce->getFieldByVariable($this->settings['field']);
        if (!$field) {
            throw new \InvalidArgumentException(
                'Given field "' . $this->settings['field'] . '" not found in DCE "' . $dce->getTitle() . '". ' .
                'Please check your routing enhancer configuration!'
            );
        }
        return $field->getValue();
    }

    public function resolve(string $value): ?string
    {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($value, 'resolve');
        // TODO: Implement resolve() method.
    }
}
