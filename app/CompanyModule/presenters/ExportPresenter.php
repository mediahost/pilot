<?php

namespace CompanyModule;

use Model\Mapper\Dibi\JobsDibiMapper;
use Model\Service\CandidateService;
use Model\Service\JobService;

class ExportPresenter extends BasePresenter
{

	/**
	 * @var CandidateService
	 */
	protected $candidates;

	/**
	 * @var JobService
	 */
	protected $jobs;

	public function injectCandidates(CandidateService $service)
	{
		$this->candidates = $service;
	}

	public function injectJobs(JobService $service)
	{
		$this->jobs = $service;
	}

	public function actionJobCandidates($id)
	{
		$job = $this->jobs->find($id);
		if (!$job || $job->company_id != $this->user->id) {
		    $this->error();
		}

		$candidates = $this->candidates->getMatched($id, TRUE, NULL, 'category, name');
		foreach ($candidates as $candidate) {
			$candidate->cv = $this->cvs->getCv($candidate->cvId);
		}
		$jobUserInfo = $this->jobs->getUserJobInfoByJob($id, TRUE);

		$xls = new \PHPExcel();
		$xls->setActiveSheetIndex(0);
		$sheet = $xls->getActiveSheet();

		$sheet->setCellValueByColumnAndRow(4, 1,'INTERVIEW NOTES:');
		foreach (['Shortlisted/Rejected', 'Name', 'Phone Number', 'Email Address', 'Communication', 'Technical', 'Other', 'General Notes', 'Interview notes', 'Status'] as $column => $title) {
			$sheet->setCellValueByColumnAndRow($column, 2, $title);
		}
		$rowNumber = 3;
		foreach ($candidates as $candidate) {
			$category = '';
			switch ($jobUserInfo[$candidate->id]['category']) {
				case JobsDibiMapper::JOB_USER_CATEGORY_SHORTLISTED:
					$category = 'Shortlisted'; break;
				case JobsDibiMapper::JOB_USER_CATEGORY_REJECTED:
					$category = 'Rejected'; break;
			}
			$sheet->setCellValueByColumnAndRow(0, $rowNumber, $category);
			$sheet->setCellValueByColumnAndRow(1, $rowNumber, $candidate->cv->getFullName());
			$sheet->setCellValueByColumnAndRow(2, $rowNumber, $candidate->cv->phone);
			$sheet->setCellValueByColumnAndRow(3, $rowNumber, $candidate->cv->email);
			$sheet->setCellValueByColumnAndRow(4, $rowNumber, $jobUserInfo[$candidate->id]['note_communication']);
			$sheet->setCellValueByColumnAndRow(5, $rowNumber, $jobUserInfo[$candidate->id]['note_technical']);
			$sheet->setCellValueByColumnAndRow(6, $rowNumber, $jobUserInfo[$candidate->id]['note_other']);
			$sheet->setCellValueByColumnAndRow(7, $rowNumber, $jobUserInfo[$candidate->id]['note_general']);
			$sheet->setCellValueByColumnAndRow(8, $rowNumber, $jobUserInfo[$candidate->id]['note_interview']);
			$sheet->setCellValueByColumnAndRow(9, $rowNumber, $this->jobs->getStatusByCompanyName($jobUserInfo[$candidate->id]['status_by_company']) ?: $jobUserInfo[$candidate->id]['status_by_company_text']);

			$rowNumber++;
		}

		$this->getHttpResponse()
			->setContentType('application/vnd.ms-excel')
			->setHeader('Content-Disposition', 'attachment; filename="candidates.xls"');
		$writer = new \PHPExcel_Writer_Excel5($xls);
		$writer->save('php://output');
		die;
	}

}