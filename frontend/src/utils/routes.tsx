export const EQUIPMENT_ENDPOINT = 'http://localhost:8080/api/equipment';
export const LOCATION_ENDPOINT = 'http://localhost:8080/api/location';

export function getSpecEquipRoute(typeName: string): string{
    return `equipment/type/${typeName}`;
}

export function getEquipRegisterRoute(typeName: string): string{
    return `equipment/register/${typeName}`;
}
