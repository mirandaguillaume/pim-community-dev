import React from 'react';
import {createHashRouter, createRoutesFromElements, Route, RouterProvider, useParams} from 'react-router-dom';
import {JobExecutionList} from './pages/JobExecutionList';
import {JobExecutionDetail} from './pages/JobExecutionDetail';

const JobExecutionDetailWrapper = () => {
  const {jobExecutionId} = useParams();
  return <JobExecutionDetail key={jobExecutionId} jobExecutionId={jobExecutionId!} />;
};

const router = createHashRouter(
  createRoutesFromElements(
    <>
      <Route path="/show/:jobExecutionId" element={<JobExecutionDetailWrapper />} />
      <Route path="/" element={<JobExecutionList />} />
    </>
  ),
  {basename: '/job'}
);

const ProcessTrackerApp = () => <RouterProvider router={router} />;

export {ProcessTrackerApp};
