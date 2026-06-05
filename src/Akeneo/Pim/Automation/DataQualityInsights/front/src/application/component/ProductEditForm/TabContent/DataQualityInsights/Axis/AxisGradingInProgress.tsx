import React, {FC} from 'react';

import __ from 'oro/translator';

const AxisGradingInProgress: FC = () => {
  return (
    <div className="AknDataQualityInsightsEvaluation">
      <span className="gradingInProgressIcon" />
      <span className="gradingInProgressMessage">
        {__(`akeneo_data_quality_insights.product_evaluation.messages.axis_grading_in_progress`)}
      </span>
    </div>
  );
};

export {AxisGradingInProgress};
