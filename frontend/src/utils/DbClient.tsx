import axios from 'axios';

interface EndpointType {
  // PMT positions integration v1
  assembly_scan: string;
  pmt_positions: string;
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
  equipment_inconsistencies: string;
  file_download: string;
  file_download_hist: string;
  file_relation: string;
  file_unrelate: string;
  statistics_inventory: string;
  statistics_status: string;
  statistics_location: string;
  statistics_institute: string;
  statistics_orphaned: string;
  statistics_location_equipments: string;
  statistics_owner: string; // Nova rota de estatísticas adicionada
  search: string;
}

const ENDPOINT: EndpointType = {
  assembly_scan: 'api/equipment/assembly-scan',
  pmt_positions: 'api/equipment/pmt-positions',
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
  equipment_inconsistencies: 'api/equipment/inconsistencies',
  file_download: 'api/file/download',
  file_download_hist: 'api/file/download-hist',
  file_relation: 'api/equipment/relate-file',
  file_unrelate: 'api/equipment/unrelate-file',
  statistics_inventory: 'api/v1/statistics/inventory-by-type',
  statistics_status: 'api/v1/statistics/global-status',
  statistics_location: 'api/v1/statistics/location-distribution',
  statistics_institute: 'api/v1/statistics/institute-distribution',
  statistics_orphaned: 'api/v1/statistics/orphaned-components',
  statistics_location_equipments: 'api/v1/statistics/location-equipments',
  statistics_owner: 'api/v1/statistics/owner-distribution', // Mapeamento da nova rota
  search: 'api/search'
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
      params: queryParams,
      headers: getRequestHeaders()
    }).then((res) => res.data);
    return response;
  }
  const response = await axios({
    url: endpoint,
    method: method,
    data: queryParams,
    headers: getRequestHeaders()
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

function getRequestHeaders(extraHeaders: any = {}) {
  let baseHeaders: any = {
    'Authorization': 'Session'
  };
  if (__ENV__ == 'local'){
    baseHeaders['TestUser'] = '';
  }
  if (extraHeaders){
    return {...baseHeaders, ...extraHeaders};
  }
  return baseHeaders;
}

/**
 * STATISTICS API CLIENT & GENERIC METHODS
 */
const DbClient = {
  // --- MÉTODOS GENÉRICOS DE CRUD ---
  get: async function(path: string) {
    // Garante que o path use a baseUrl (útil para o EquipInfo.tsx)
    const url = path.startsWith('http') ? path : `${__APP_BASEURL__}${path.startsWith('/') ? '' : '/'}${path}`;
    return axios.get(url, { headers: getRequestHeaders() });
  },

  patch: async function(path: string, data: any) {
    const url = path.startsWith('http') ? path : `${__APP_BASEURL__}${path.startsWith('/') ? '' : '/'}${path}`;
    return axios.patch(url, data, { headers: getRequestHeaders() });
  },

  put: async function(path: string, data: any) {
    const url = path.startsWith('http') ? path : `${__APP_BASEURL__}${path.startsWith('/') ? '' : '/'}${path}`;
    return axios.put(url, data, { headers: getRequestHeaders() });
  },

  // --- MÉTODOS ESPECÍFICOS DE ESTATÍSTICAS ---
  getInventoryByType: async function() {
    try {
      const url = getEndpoint('statistics_inventory');
      const response = await axios.get(url, { headers: getRequestHeaders() });
      return response.data;
    } catch (error) {
      console.error("Error fetching inventory statistics:", error);
      throw error;
    }
  },

  getGlobalStatusDistribution: async function() {
    try {
      const url = getEndpoint('statistics_status');
      const response = await axios.get(url, { headers: getRequestHeaders() });
      return response.data;
    } catch (error) {
      console.error("Error fetching status statistics:", error);
      throw error;
    }
  },

  getLocationDistribution: async function() {
    try {
      const url = getEndpoint('statistics_location');
      const response = await axios.get(url, { headers: getRequestHeaders() });
      return response.data;
    } catch (error) {
      console.error("Error fetching location statistics:", error);
      throw error;
    }
  },

  getInstituteDistribution: async function() {
    try {
      const url = getEndpoint('statistics_institute');
      const response = await axios.get(url, { headers: getRequestHeaders() });
      return response.data;
    } catch (error) {
      console.error("Error fetching institute statistics:", error);
      throw error;
    }
  },

  getOwnerDistribution: async function() {
    try {
      const url = getEndpoint('statistics_owner');
      const response = await axios.get(url, { headers: getRequestHeaders() });
      return response.data;
    } catch (error) {
      console.error("Error fetching owner statistics:", error);
      throw error;
    }
  },

  getOrphanedComponents: async function() {
    try {
      const url = getEndpoint('statistics_orphaned');
      const response = await axios.get(url, { headers: getRequestHeaders() });
      return response.data;
    } catch (error) {
      console.error("Error fetching orphaned components:", error);
      throw error;
    }
  },

  getEquipmentsByLocation: async function(locationName: string) {
    try {
      const url = `${getEndpoint('statistics_location_equipments')}?location=${encodeURIComponent(locationName)}`;
      const response = await axios.get(url, { headers: getRequestHeaders() });
      return response.data;
    } catch (error) {
      console.error("Error fetching location details:", error);
      throw error;
    }
  }
};

export default DbClient;