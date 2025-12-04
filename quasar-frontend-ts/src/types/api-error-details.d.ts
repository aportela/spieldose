interface APIErrorDetails {
  method: string;
  url: string;
  httpCode: number;
  httpStatus: string;
  contentType: string;
  request: {
    params: {
      query: string | null;
      data: string | null;
    };
  };
  response: string | null;
};

export { type APIErrorDetails };
