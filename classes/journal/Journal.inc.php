<?php

/**
 * @defgroup journal
 */

/**
 * @file classes/journal/Journal.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Journal
 * @ingroup journal
 * @see JournalDAO
 *
 * @brief Describes basic journal properties.
 */


define('PUBLISHING_MODE_OPEN', 0);
define('PUBLISHING_MODE_SUBSCRIPTION', 1);
define('PUBLISHING_MODE_NONE', 2);

class Journal extends DataObject {
	/**
	 * Constructor.
	 */
	function Journal() {
		parent::DataObject();
	}

	/**
	 * Get the base URL to the journal.
	 * @return string
	 */
	function getUrl() {
		return Request::url($this->getPath());
	}

	/**
	 * Return the primary locale of this journal.
	 * @return string
	 */
	function getPrimaryLocale() {
		return $this->getData('primaryLocale');
	}

	/**
	 * Set the primary locale of this journal.
	 * @param $locale string
	 */
	function setPrimaryLocale($primaryLocale) {
		return $this->setData('primaryLocale', $primaryLocale);
	}

	/**
	 * Return associative array of all locales supported by the journal.
	 * These locales are used to provide a language toggle on the journal-specific pages.
	 * @return array
	 */
	function &getSupportedLocaleNames() {
		$supportedLocales =& $this->getData('supportedLocales');

		if (!isset($supportedLocales)) {
			$supportedLocales = array();
			$localeNames =& AppLocale::getAllLocales();

			$locales = $this->getSetting('supportedLocales');
			if (!isset($locales) || !is_array($locales)) {
				$locales = array();
			}

			foreach ($locales as $localeKey) {
				$supportedLocales[$localeKey] = $localeNames[$localeKey];
			}
		}

		return $supportedLocales;
	}

	/**
	 * Return associative array of all locales supported by forms of the journal.
	 * These locales are used to provide a language toggle on the journal-specific pages.
	 * @return array
	 */
	function &getSupportedFormLocaleNames() {
		$supportedLocales =& $this->getData('supportedFormLocales');

		if (!isset($supportedLocales)) {
			$supportedLocales = array();
			$localeNames =& AppLocale::getAllLocales();

			$locales = $this->getSetting('supportedFormLocales');
			if (!isset($locales) || !is_array($locales)) {
				$locales = array();
			}

			foreach ($locales as $localeKey) {
				$supportedLocales[$localeKey] = $localeNames[$localeKey];
			}
		}

		return $supportedLocales;
	}

	/**
	 * Return associative array of all locales supported for the submissions.
	 * These locales are used to provide a language toggle on the submission setp1 and the galley edit page.
	 * @return array
	 */
	function &getSupportedSubmissionLocaleNames() {
		$supportedLocales =& $this->getData('supportedSubmissionLocales');

		if (!isset($supportedLocales)) {
			$supportedLocales = array();
			$localeNames =& AppLocale::getAllLocales();

			$locales = $this->getSetting('supportedSubmissionLocales');
			if (empty($locales)) $locales = array($this->getPrimaryLocale());

			foreach ($locales as $localeKey) {
				$supportedLocales[$localeKey] = $localeNames[$localeKey];
			}
		}

		return $supportedLocales;
	}

	/**
	 * Get "localized" journal page title (if applicable).
	 * param $home boolean get homepage title
	 * @return string
	 */
	function getLocalizedPageHeaderTitle($home = false) {
		$prefix = $home ? 'home' : 'page';
		$typeArray = $this->getSetting($prefix . 'HeaderTitleType');
		$imageArray = $this->getSetting($prefix . 'HeaderTitleImage');
		$titleArray = $this->getSetting($prefix . 'HeaderTitle');

		$title = null;

		foreach (array(AppLocale::getLocale(), AppLocale::getPrimaryLocale()) as $locale) {
			if (isset($typeArray[$locale]) && $typeArray[$locale]) {
				if (isset($imageArray[$locale])) $title = $imageArray[$locale];
			}
			if (empty($title) && isset($titleArray[$locale])) $title = $titleArray[$locale];
			if (!empty($title)) return $title;
		}
		return null;
	}

	/**
	 * Get "localized" journal page logo (if applicable).
	 * param $home boolean get homepage logo
	 * @return string
	 */
	function getLocalizedPageHeaderLogo($home = false) {
		$prefix = $home ? 'home' : 'page';
		$logoArray = $this->getSetting($prefix . 'HeaderLogoImage');
		foreach (array(AppLocale::getLocale(), AppLocale::getPrimaryLocale()) as $locale) {
			if (isset($logoArray[$locale])) return $logoArray[$locale];
		}
		return null;
	}

	/**
	 * Get localized favicon
	 * @return string
	 */
	function getLocalizedFavicon() {
		$faviconArray = $this->getSetting('journalFavicon');
		foreach (array(AppLocale::getLocale(), AppLocale::getPrimaryLocale()) as $locale) {
			if (isset($faviconArray[$locale])) return $faviconArray[$locale];
		}
		return null;
	}

	//
	// Get/set methods
	//

	/**
	 * Get the localized title of the journal.
	 * @param $preferredLocale string
	 * @return string
	 */
	function getLocalizedTitle($preferredLocale = null) {
		return $this->getLocalizedSetting('title', $preferredLocale);
	}

	/**
	 * Get title of journal
	 * @param $locale string
	 * @return string
	 */
	function getTitle($locale) {
		return $this->getSetting('title', $locale);
	}

	/**
	 * Get localized initials of journal
	 * @return string
	 */
	function getLocalizedInitials() {
		return $this->getLocalizedSetting('initials');
	}

	/**
	 * Get the initials of the journal.
	 * @param $locale string
	 * @return string
	 */
	function getInitials($locale) {
		return $this->getSetting('initials', $locale);
	}

	/**
	 * Get enabled flag of journal
	 * @return int
	 */
	function getEnabled() {
		return $this->getData('enabled');
	}

	/**
	 * Set enabled flag of journal
	 * @param $enabled int
	 */
	function setEnabled($enabled) {
		return $this->setData('enabled',$enabled);
	}

	/**
	 * Get the localized description of the journal.
	 * @return string
	 */
	function getLocalizedDescription() {
		return $this->getDescription(AppLocale::getLocale());
	}

	/**
	 * Get description of journal.
	 * @param $locale string
	 * @return string
	 */
	function getDescription($locale) {
		return $this->getSetting('description', $locale);
	}

	/**
	 * Get path to journal (in URL).
	 * @return string
	 */
	function getPath() {
		return $this->getData('path');
	}

	/**
	 * Set path to journal (in URL).
	 * @param $path string
	 */
	function setPath($path) {
		return $this->setData('path', $path);
	}

	/**
	 * Get sequence of journal in site table of contents.
	 * @return float
	 */
	function getSequence() {
		return $this->getData('sequence');
	}

	/**
	 * Set sequence of journal in site table of contents.
	 * @param $sequence float
	 */
	function setSequence($sequence) {
		return $this->setData('sequence', $sequence);
	}

	/**
	 * Retrieve array of journal settings.
	 * @return array
	 */
	function &getSettings() {
		$journalSettingsDao =& DAORegistry::getDAO('JournalSettingsDAO');
		$settings =& $journalSettingsDao->getJournalSettings($this->getId());
		return $settings;
	}

	/**
	 * Retrieve a localized setting.
	 * @param $name string
	 * @param $preferredLocale string
	 * @return mixed
	 */
	function &getLocalizedSetting($name, $preferredLocale = null) {
		if (is_null($preferredLocale)) $preferredLocale = AppLocale::getLocale();
		$returner = $this->getSetting($name, $preferredLocale);
		if ($returner === null) {
			unset($returner);
			$returner = $this->getSetting($name, AppLocale::getPrimaryLocale());
		}
		return $returner;
	}

	/**
	 * Retrieve a journal setting value.
	 * @param $name string
	 * @param $locale string
	 * @return mixed
	 */
	function &getSetting($name, $locale = null) {
		$journalSettingsDao =& DAORegistry::getDAO('JournalSettingsDAO');
		$setting =& $journalSettingsDao->getSetting($this->getId(), $name, $locale);
		return $setting;
	}

	/**
	 * Update a journal setting value.
	 * @param $name string
	 * @param $value mixed
	 * @param $type string optional
	 * @param $isLocalized boolean optional
	 */
	function updateSetting($name, $value, $type = null, $isLocalized = false) {
		$journalSettingsDao =& DAORegistry::getDAO('JournalSettingsDAO');
		return $journalSettingsDao->updateSetting($this->getId(), $name, $value, $type, $isLocalized);
	}
}

?>
