<?php

/**
 * @file controllers/grid/issues/form/IssueForm.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueForm
 * @ingroup controllers_grid_issues_form
 * @see Issue
 *
 * @brief Form to create or edit an issue
 */

import('lib.pkp.classes.form.Form');
import('classes.issue.Issue'); // Bring in constants

class IssueForm extends Form {
	/** @var Issue current issue */
	var $issue;

	/**
	 * Constructor.
	 */
	function IssueForm($issue = null) {
		parent::Form('controllers/grid/issues/form/issueForm.tpl');
		$this->addCheck(new FormValidatorCustom($this, 'showVolume', 'optional', 'editor.issues.volumeRequired', create_function('$showVolume, $form', 'return !$showVolume || $form->getData(\'volume\') ? true : false;'), array($this)));
		$this->addCheck(new FormValidatorCustom($this, 'showNumber', 'optional', 'editor.issues.numberRequired', create_function('$showNumber, $form', 'return !$showNumber || $form->getData(\'number\') ? true : false;'), array($this)));
		$this->addCheck(new FormValidatorCustom($this, 'showYear', 'optional', 'editor.issues.yearRequired', create_function('$showYear, $form', 'return !$showYear || $form->getData(\'year\') ? true : false;'), array($this)));
		$this->addCheck(new FormValidatorCustom($this, 'showTitle', 'optional', 'editor.issues.titleRequired', create_function('$showTitle, $form', 'return !$showTitle || implode(\'\', $form->getData(\'title\'))!=\'\' ? true : false;'), array($this)));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
		$this->issue = $issue;
	}

	/**
	 * Get a list of fields for which localization should be used.
	 * @return array
	 */
	function getLocaleFieldNames() {
		$issueDao = DAORegistry::getDAO('IssueDAO');
		return $issueDao->getLocaleFieldNames();
	}

	/**
	 * Fetch the form.
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$journal = $request->getJournal();

		// set up the accessibility options pulldown
		$templateMgr->assign('enableDelayedOpenAccess', $journal->getSetting('enableDelayedOpenAccess'));

		$templateMgr->assign('accessOptions', array(
			ISSUE_ACCESS_OPEN => AppLocale::Translate('editor.issues.openAccess'),
			ISSUE_ACCESS_SUBSCRIPTION => AppLocale::Translate('editor.issues.subscription')
		));

		if ($this->issue) {
			$templateMgr->assign('issue', $this->issue);
			$templateMgr->assign('issueId', $this->issue->getId());
		}

		// Cover image preview
		$coverImage = $this->issue->getLocalizedCoverImage();

		// Cover image delete link action
		if ($coverImage) {
			import('lib.pkp.classes.linkAction.LinkAction');
			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			$router = $request->getRouter();
			$deleteCoverImageLinkAction = new LinkAction(
				'deleteCoverImage',
				new RemoteActionConfirmationModal(
					$request->getSession(),
					__('common.confirmDelete'), null,
					$router->url(
						$request, null, null, 'deleteCoverImage', null, array(
							'coverImage' => $coverImage,
							'issue' => $this->issue->getId(),
						)
					),
					'modal_delete'
				),
				__('common.delete'),
				null
			);
			$templateMgr->assign('deleteCoverImageLinkAction', $deleteCoverImageLinkAction);
		}

		return parent::fetch($request);
	}

	/**
	 * Validate the form
	 */
	function validate($request) {
		if ($temporaryFileId = $this->getData('temporaryFileId')) {
			$user = $request->getUser();
			$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
			$temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $user->getId());

			import('classes.file.PublicFileManager');
			$publicFileManager = new PublicFileManager();
			if (!$publicFileManager->getImageExtension($temporaryFile->getFileType())) {
				$this->addError('coverImage', __('editor.issues.invalidCoverImageFormat'));
			}
		}

		return parent::validate();
	}

	/**
	 * Initialize form data from current issue.
	 */
	function initData($request) {
		if (isset($this->issue)) {
			$this->_data = array(
				'title' => $this->issue->getTitle(null), // Localized
				'volume' => $this->issue->getVolume(),
				'number' => $this->issue->getNumber(),
				'year' => $this->issue->getYear(),
				'datePublished' => $this->issue->getDatePublished(),
				'description' => $this->issue->getDescription(null), // Localized
				'accessStatus' => $this->issue->getAccessStatus(),
				'openAccessDate' => $this->issue->getOpenAccessDate(),
				'showVolume' => $this->issue->getShowVolume(),
				'showNumber' => $this->issue->getShowNumber(),
				'showYear' => $this->issue->getShowYear(),
				'showTitle' => $this->issue->getShowTitle(),
				'coverImage' => $this->issue->getLocalizedCoverImage(), // Localized
				'coverImageAltText' => $this->issue->getCoverImageAltText(null), // Localized
			);
			parent::initData();
		} else {
			$journal = $request->getJournal();
			switch ($journal->getSetting('publishingMode')) {
				case PUBLISHING_MODE_SUBSCRIPTION:
				case PUBLISHING_MODE_NONE:
					$accessStatus = ISSUE_ACCESS_SUBSCRIPTION;
					break;
				case PUBLISHING_MODE_OPEN:
				default:
					$accessStatus = ISSUE_ACCESS_OPEN;
					break;
			}

			$this->_data = array(
				'showVolume' => 1,
				'showNumber' => 1,
				'showYear' => 1,
				'showTitle' => 1,
				'accessStatus' => $accessStatus
			);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
			'title',
			'volume',
			'number',
			'year',
			'description',
			'accessStatus',
			'enableOpenAccessDate',
			'showVolume',
			'showNumber',
			'showYear',
			'showTitle',
			'temporaryFileId',
			'coverImageAltText',
		));

		$this->readUserDateVars(array('datePublished', 'openAccessDate'));

		$this->addCheck(new FormValidatorCustom($this, 'issueForm', 'required', 'editor.issues.issueIdentificationRequired', create_function('$showVolume, $showNumber, $showYear, $showTitle', 'return $showVolume || $showNumber || $showYear || $showTitle ? true : false;'), array($this->getData('showNumber'), $this->getData('showYear'), $this->getData('showTitle'))));

	}

	/**
	 * Save issue settings.
	 * @param $request PKPRequest
	 * @return int Issue ID for created/updated issue
	 */
	function execute($request) {
		$journal = $request->getJournal();

		$issueDao = DAORegistry::getDAO('IssueDAO');
		if ($this->issue) {
			$isNewIssue = false;
			$issue = $this->issue;
		} else {
			$issue = $issueDao->newDataObject();
			$isNewIssue = true;
		}
		$volume = $this->getData('volume');
		$number = $this->getData('number');
		$year = $this->getData('year');

		$issue->setJournalId($journal->getId());
		$issue->setTitle($this->getData('title'), null); // Localized
		$issue->setVolume(empty($volume) ? 0 : $volume);
		$issue->setNumber(empty($number) ? 0 : $number);
		$issue->setYear(empty($year) ? 0 : $year);
		if (!$isNewIssue) {
			$issue->setDatePublished($this->getData('datePublished'));
		}
		$issue->setDescription($this->getData('description'), null); // Localized
		$issue->setShowVolume($this->getData('showVolume'));
		$issue->setShowNumber($this->getData('showNumber'));
		$issue->setShowYear($this->getData('showYear'));
		$issue->setShowTitle($this->getData('showTitle'));

		$issue->setAccessStatus($this->getData('accessStatus') ? $this->getData('accessStatus') : ISSUE_ACCESS_OPEN); // See bug #6324
		if ($this->getData('enableOpenAccessDate')) $issue->setOpenAccessDate($this->getData('openAccessDate'));
		else $issue->setOpenAccessDate(null);

		// Copy an uploaded cover file for the issue, if there is one.
		if ($temporaryFileId = $this->getData('temporaryFileId')) {
			$user = $request->getUser();
			$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
			$temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $user->getId());

			import('classes.file.PublicFileManager');
			$publicFileManager = new PublicFileManager();
			$newFileName = 'cover_issue_' . $issue->getId() . '_' . $this->getFormLocale() . $publicFileManager->getImageExtension($temporaryFile->getFileType());
			$journal = $request->getJournal();
			$publicFileManager->copyJournalFile($journal->getId(), $temporaryFile->getFilePath(), $newFileName);
			$issue->setCoverImage($newFileName, $this->getFormLocale());
			$issueDao->updateObject($issue);
		}

		$issue->setCoverImageAltText($this->getData('coverImageAltText'), null); // Localized

		// if issueId is supplied, then update issue otherwise insert a new one
		if (!$isNewIssue) {
			parent::execute();
			$issueDao->updateObject($issue);
		} else {
			$issue->setPublished(0);
			$issue->setCurrent(0);

			$issueDao->insertObject($issue);
		}
	}
}

?>
