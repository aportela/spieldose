import { type AxiosResponse } from "axios";
import { type EnvironmentType, type ValidAuthTypes } from "./common";

interface DefaultAxiosResponse<T = unknown> {
  data: AxiosResponse<T>;
}

interface getServerEnvironmentResponseData {
  data: {
    serverEnvironment: {
      allowSignUp: boolean;
      environment: EnvironmentType;
      maxUploadFileSize: number;
    }
  }
};

interface LoginResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    accessToken: string;
    refreshToken: string;
    tokenType: ValidAuthTypes;
  }
};

interface GetNewAccessTokenResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    accessToken: string;
    tokenType: ValidAuthTypes;
  }
};

interface RegisterResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: undefined;
};

interface UserProfileResponseData {
  id: string | null;
  email: string;
};

interface GetProfileResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    user: UserProfileResponseData;
  }
};

interface SetProfileResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    user: UserProfileResponseData;
  }
};

export {
  getServerEnvironmentResponseData,
  DefaultAxiosResponse,
  LoginResponse,
  GetNewAccessTokenResponse,
  RegisterResponse,
  GetProfileResponse,
  SetProfileResponse,
};
