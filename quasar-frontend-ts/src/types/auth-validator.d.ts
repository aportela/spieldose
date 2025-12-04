import { type ValidatorField as ValidatorFieldInterface } from "src/types/validator-field";

interface AuthValidator {
  email: ValidatorFieldInterface;
  password: ValidatorFieldInterface;
};

const defaultAuthValidator: AuthValidator = {
  email: {
    hasErrors: false,
    message: null
  },
  password: {
    hasErrors: false,
    message: null
  }
};

export { type AuthValidator, defaultAuthValidator };
