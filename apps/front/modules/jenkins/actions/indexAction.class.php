<?php

class indexAction extends baseJenkinsAction
{

  /**
   * @param sfRequest $request The current sfRequest object
   * @return mixed     A string containing the view name associated with this action
   */
  function execute($request)
  {
    $userId     = $this->getUser()->getUserId();
    
    if ($request->hasParameter('git_branch_slug'))
    {
      $groupRun   = JenkinsGroupRunPeer::retrieveBySfGuardUserIdAndGitBranchSlug($userId, $request->getParameter('git_branch_slug'));
      $currentGroupId = $groupRun->getId();
    }
    else
    {
      $currentGroupId   = $request->getParameter('group_run_id');
    }

    $jenkins          = $this->getJenkins();
    $criteriaGroupRun = new Criteria();
    $criteriaGroupRun->add(JenkinsGroupRunPeer::SF_GUARD_USER_ID, $userId, Criteria::EQUAL);
    $criteriaGroupRun->addDescendingOrderByColumn(JenkinsGroupRunPeer::DATE);
    $criteriaGroupRun->addDescendingOrderByColumn(JenkinsGroupRunPeer::ID);

    $groupRuns     = JenkinsGroupRunPeer::doSelect($criteriaGroupRun);

    $dataGroupRuns = array();
    foreach ($groupRuns as $groupRun)
    {
      if (null === $currentGroupId)
      {
        $currentGroupId = $groupRun->getId();
      }

      /** @var JenkinsGroupRun $groupRun */
      $dataGroupRuns[$groupRun->getId()] = array(
        'label'           => $groupRun->getLabel(),
        'git_branch'      => $groupRun->getGitBranch(),
        'git_branch_slug' => $groupRun->getGitBranchSlug(),
        'date'            => $groupRun->getDate('d/m/Y H:i:s'),
        'result'          => $groupRun->getResult($jenkins),
        'url_view'        => $this->generateUrl('branch_view', $groupRun),
      );
    }

    $this->setVar('group_runs', $dataGroupRuns);
    $this->setVar('current_group_run_id', $currentGroupId);
  }

}
