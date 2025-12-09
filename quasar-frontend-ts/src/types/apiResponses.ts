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

interface PagerResponse {
  totalResults: number;
  totalPages: number;
}

interface BrowseArtistItemResponse {
  name: string;
  mbId: string | null;
  image: string | null;
  totalTracks: number;
}

interface BrowseArtistsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    pager: PagerResponse;
    artists: BrowseArtistItemResponse[];
  }
};

interface BrowseAlbumItemResponse {
  title: string;
  mbId: string | null;
  year: number | null;
  image: string | null;
}

interface BrowseAlbumsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    pager: PagerResponse;
    albums: BrowseAlbumItemResponse[];
  }
};

interface AddPlayListResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    playList: {
      id: string;
      name: string;
    };
  }
};

interface PlayList {
  id: string;
  name: string;
  createdAt: number;
  updatedAt: number | null;
}

interface GetCurrentPlayListsResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    playLists: PlayList[],
  }
};

export {
  type getServerEnvironmentResponseData,
  type DefaultAxiosResponse,
  type LoginResponse,
  type GetNewAccessTokenResponse,
  type RegisterResponse,
  type GetProfileResponse,
  type SetProfileResponse,
  type PagerResponse,
  type BrowseArtistItemResponse,
  type BrowseArtistsResponse,
  type BrowseAlbumItemResponse,
  type BrowseAlbumsResponse,
  type AddPlayListResponse,
  type GetCurrentPlayListsResponse,
};
