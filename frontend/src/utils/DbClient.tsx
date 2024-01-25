import axios from 'axios';

interface EndpointType {
    equipment: string;
    location: string;
    institute: string;
    status: string;
    detector_location: string;
    file: string;
}

export const ENDPOINT: EndpointType = {
    equipment: 'http://localhost:8080/api/equipment',
    location: 'http://localhost:8080/api/location',
    institute: 'http://localhost:8080/api/institute',
    status: 'http://localhost:8080/api/status',
    detector_location: 'http://localhost:8080/api/detector-location',
    file: 'http://localhost:8080/api/file',
};

interface GetInputType {
    queryKey: Array<any>;
}

const parseQueryKey = (queryKey: any[]) => {
  const endpoint = ENDPOINT[queryKey[0] as keyof EndpointType];
  const queryParams = queryKey[1];
  return {endpoint: endpoint, queryParams: queryParams};
}

export function makeQueryFunction(method: string){
  async function queryFunction({queryKey}: GetInputType){
    const {endpoint, queryParams} = parseQueryKey(queryKey);
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
  return queryFunction;
}

export function makeMutationFunction(method: string, endpoint: string){
    async function mutationFunction(data: any){
        const response = await axios({
            url: endpoint,
            method: method,
            data: data
        }).then((res) => res.data);
        return response.data;
    }
    return mutationFunction;
}