import type { APIErrorDetails } from "./api-error-details";

interface AjaxState {
  ajaxRunning: boolean;
  ajaxErrors: boolean;
  ajaxErrorMessage: string | null;
  ajaxAPIErrorDetails: APIErrorDetails | null;
};

const defaultAjaxState: AjaxState = {
  ajaxRunning: false,
  ajaxErrors: false,
  ajaxErrorMessage: null,
  ajaxAPIErrorDetails: null,
};

export { type AjaxState, defaultAjaxState };
