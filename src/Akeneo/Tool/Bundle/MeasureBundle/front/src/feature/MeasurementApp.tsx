import React from 'react';
import {createHashRouter, createRoutesFromElements, Route, RouterProvider} from 'react-router-dom';
import {Edit} from './pages/edit';
import {List} from './pages/list';

const router = createHashRouter(
  createRoutesFromElements(
    <>
      <Route path="/:measurementFamilyCode" element={<Edit />} />
      <Route path="/" element={<List />} />
    </>
  ),
  {basename: '/configuration/measurement'}
);

const MeasurementApp = () => {
  return <RouterProvider router={router} />;
};

export {MeasurementApp};
