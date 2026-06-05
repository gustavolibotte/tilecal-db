export function getSpecEquipRoute(typeName: string): string{
    return `/equipment/type/${typeName}`;
}

export function getEquipRegisterRoute(typeName: string): string{
    return `/equipment/register/${typeName}`;
}

export function getEquipviewRoute(id: string): string{
    return `/equipment/${id}`;
}

export function getFileViewRoute(id: string): string{
    return `/file/${id}`;
}

export function concatenateUrlParams(url: string, params: any): string{
    url += '?'
    const keyValuePairs = Object.keys(params).map((key) => {
        return `${key}=${params[key]}`;
    })
    url += keyValuePairs.join('&');
    return url;
}

export const ROUTES = {
    STATISTICS: '/statistics',
    QC_PMT_CONSISTENCY: '/qc-pmt-consistency',
    SEARCH: '/search',
};