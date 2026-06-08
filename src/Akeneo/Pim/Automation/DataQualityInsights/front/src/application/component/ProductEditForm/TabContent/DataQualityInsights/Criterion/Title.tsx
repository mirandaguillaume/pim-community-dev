import React, {FC} from 'react';

import translate from 'oro/translator';

type Props = {
  criterion?: string;
};
const Title: FC<Props> = ({criterion}) => {
  return (
    <span className="CriterionRecommendationMessage">
      {criterion && translate(`akeneo_data_quality_insights.product_evaluation.criteria.${criterion}.recommendation`)}
      :&nbsp;
    </span>
  );
};
export {Title};
