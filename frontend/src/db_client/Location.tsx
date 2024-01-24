import axios from 'axios';

export const LOCATION_ENDPOINT = 'http://localhost:8080/api/location';

interface GetInputType {
    queryKey: Array<any>;
}

export interface LocationType {
    name?: string;
    description?:  string;
}

export async function locationGet({queryKey}: GetInputType){
    const queryParams = queryKey[1];
    const response = await axios({
        url: LOCATION_ENDPOINT,
        method: 'get',
        data: queryParams
    }).then((res) => res.data);
    return response.data;
}