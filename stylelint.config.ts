import { Config } from 'stylelint';

const stylelintConfig: Config = {
  extends: 'stylelint-config-recommended',
  rules: {
    'prettier/stylelintIntegration': true,
    'at-rule-no-unknown': [
      true,
      {
        ignoreAtRules: ['tailwind', 'apply', 'variants', 'responsive', 'screen'],
      },
    ],
    'selector-pseudo-class-no-unknown': [
      true,
      {
        ignorePseudoClasses: ['global'],
      },
    ],
  },
};

export default stylelintConfig;
