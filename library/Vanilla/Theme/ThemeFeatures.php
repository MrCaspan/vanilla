<?php
/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Theme;

use Vanilla\Contracts\AddonInterface;
use Vanilla\Contracts\ConfigurationInterface;

/**
 * Class to hold information about a theme and it's configuration options.
 */
class ThemeFeatures implements \JsonSerializable {

    /** @var AddonInterface */
    private $theme;

    /** @var ConfigurationInterface */
    private $config;

    private $forcedFeatures = [];

    const FEATURE_DEFAULTS = [
        'NewFlyouts' => false,
        'SharedMasterView' => false,
        'ProfileHeader' => false,
        'DataDrivenTheme' => false,
        'DisableKludgedVars' => false,
    ];

    /**
     * Constuctor.
     *
     * @param ConfigurationInterface $config
     * @param AddonInterface $theme
     */
    public function __construct(ConfigurationInterface $config, AddonInterface $theme) {
        $this->config = $config;
        $this->theme = $theme;
    }

    /**
     * Force some theme features to be active.
     *
     * @param array $forcedFeatures An array of Feature => boolean.
     */
    public function forceFeatures(array $forcedFeatures) {
        $this->forcedFeatures = $forcedFeatures;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize() {
        return $this->allFeatures();
    }


    /**
     * Get all of the current theme features.
     */
    public function allFeatures(): array {
        if ($this->theme === null) {
            return self::FEATURE_DEFAULTS;
        }
        $configValues = [
            'NewFlyouts' => $this->config->get('Feature.NewFlyouts.Enabled'),
        ];
        $themeValues = $this->theme->getInfoValue('Features', []);
        if ($themeValues['DataDrivenTheme'] ?? false) {
            // Data driven themes automatically enables other theme features.
            $themeValues['DisableKludgedVars'] = true;
            $themeValues['ProfileHeader'] = true;
            $themeValues['SharedMasterView'] = true;
            $themeValues['NewFlyouts'] = true;
        }

        return array_merge(self::FEATURE_DEFAULTS, $configValues, $themeValues, $this->forcedFeatures);
    }

    /**
     * @return bool
     */
    public function useNewFlyouts(): bool {
        return (bool) $this->allFeatures()['NewFlyouts'];
    }

    /**
     * @return bool
     */
    public function useSharedMasterView(): bool {
        return (bool) $this->allFeatures()['SharedMasterView'];
    }

    /**
     * @return bool
     */
    public function useProfileHeader(): bool {
        return (bool) $this->allFeatures()['ProfileHeader'];
    }

    /**
     * @return bool
     */
    public function useDataDrivenTheme(): bool {
        return (bool) $this->allFeatures()['DataDrivenTheme'];
    }

    /**
     * @return bool
     */
    public function disableKludgedVars(): bool {
        return (bool) $this->allFeatures()['DisableKludgedVars'];
    }
}
