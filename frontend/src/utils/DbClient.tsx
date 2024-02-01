import axios from 'axios';

interface EndpointType {
  equipment: string;
  location: string;
  institute: string;
  status: string;
  detector_location: string;
  file: string;
  equipment_hist: string;
  file_hist: string;
  file_put: string;
  user: string;
  equipment_type: string;
}

export const ENDPOINT: EndpointType = {
  equipment: 'http://localhost:8080/api/equipment',
  location: 'http://localhost:8080/api/location',
  institute: 'http://localhost:8080/api/institute',
  status: 'http://localhost:8080/api/status',
  detector_location: 'http://localhost:8080/api/detector-location',
  file: 'http://localhost:8080/api/file',
  equipment_hist: 'http://localhost:8080/api/equipment-hist',
  file_hist: 'http://localhost:8080/api/file-hist',
  file_put: 'http://localhost:8080/api/file-put',
  user: 'http://localhost:8080/api/user',
  equipment_type: 'http://localhost:8080/api/equipment-type',
};

interface GetInputType {
  queryKey: any;
}

export type AppResponseType = SuccessResponseType;

interface SuccessResponseType {
  data: Array<any>;
  status: number;
}

const parseQueryKey = (queryKey: any[]) => {
  const endpoint = ENDPOINT[queryKey[0] as keyof EndpointType];
  const method = queryKey[1];
  const queryParams = queryKey[2];
  return {endpoint: endpoint, method: method, queryParams: queryParams};
}

export async function defaultQueryFunction({queryKey}: GetInputType): Promise<AppResponseType>{
  const {endpoint, method, queryParams} = parseQueryKey(queryKey);
  if (method == 'get'){
    const response = await axios({
      url: endpoint,
      method: method,
      params: queryParams
    }).then((res) => res.data);
    return response;
  }
  const response = await axios({
    url: endpoint,
    method: method,
    data: queryParams
  }).then((res) => res.data);
  return response;
}

export function makeMutationFunction(method: string, endpoint: string, headers: any = null){
  async function mutationFunction(data: any){
      const response = await axios({
          url: endpoint,
          method: method,
          data: data,
          headers: headers
      }).then((res) => res.data);
      return response.data;
  }
  return mutationFunction;
}