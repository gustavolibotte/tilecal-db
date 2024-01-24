import axios from 'axios';
export const LOCATION_ENDPOINT = 'http://localhost:8080/api/location';
export const INSTITUTE_ENDPOINT = 'http://localhost:8080/api/institute';


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