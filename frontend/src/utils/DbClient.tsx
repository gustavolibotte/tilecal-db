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
  equipment_batch: string;
  equipment_assemble: string;
  equipment_disassemble: string;
  file_download: string;
  file_download_hist: string;
}

const ENDPOINT: EndpointType = {
  equipment: 'api/equipment',
  location: 'api/location',
  institute: 'api/institute',
  status: 'api/status',
  detector_location: 'api/detector-location',
  file: 'api/file',
  equipment_hist: 'api/equipment-hist',
  file_hist: 'api/file-hist',
  file_put: 'api/file-put',
  user: 'api/user',
  equipment_type: 'api/equipment-type',
  equipment_batch: 'api/equipment/batch-register',
  equipment_assemble: 'api/equipment/assemble',
  equipment_disassemble: 'api/equipment/disassemble',
  file_download: 'api/file/download',
  file_download_hist: 'api/file/download-hist'
};

export function getEndpoint(name: keyof EndpointType): string{
  const url =  `${__APP_BASEURL__}/${ENDPOINT[name]}`;
  return url;
}

interface GetInputType {
  queryKey: any;
}

export type AppResponseType = SuccessResponseType;

interface SuccessResponseType {
  data: Array<any>;
  status: number;
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
          headers: getRequestHeaders(headers)
      }).then((res) => res.data);
      return response.data;
  }
  return mutationFunction;
}

function parseQueryKey(queryKey: any[]) {
  let endpoint;
  if (queryKey[0] == 'equipment_type'){
    endpoint = `${__APP_BASEURL__}/api/equipment-type`;
  } else{
    endpoint = getEndpoint(queryKey[0] as keyof EndpointType);
  }
  const method = queryKey[1];
  const queryParams = queryKey[2];
  return {endpoint: endpoint, method: method, queryParams: queryParams};
}

function getRequestHeaders(extraHeaders: any) {
  let baseHeaders: any = {
    'X-Requested-With': 'XMLHttpRequest',
    'Authorization': 'Bearer '
  };
  if (__ENV__ === 'local'){
    baseHeaders['TestUser'] = '';
  }
  if (extraHeaders){
    return {...baseHeaders, ...extraHeaders};
  }
  return baseHeaders;
}