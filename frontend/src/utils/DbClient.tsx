import axios from 'axios';
export const EQUIPMENT_ENDPOINT = 'http://localhost:8080/api/equipment';
export const LOCATION_ENDPOINT = 'http://localhost:8080/api/location';
export const INSTITUTE_ENDPOINT = 'http://localhost:8080/api/institute';
export const STATUS_ENDPOINT = 'http://localhost:8080/api/status';
export const DETECTOR_LOCATION_ENDPOINT = 'http://localhost:8080/api/detector-location';


interface GetInputType {
    queryKey: Array<any>;
}

export function makeQueryFunction(method: string, endpoint: string){
    async function queryFunction({queryKey}: GetInputType){
        const queryParams = queryKey[1];
        const response = await axios({
            url: endpoint,
            method: method,
            data: queryParams
        }).then((res) => res.data);
        return response.data;
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