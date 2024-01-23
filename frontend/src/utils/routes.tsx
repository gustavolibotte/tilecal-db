export const EQUIPMENT_ENDPOINT = 'http://localhost:8080/api/equipment';

export function getSpecEquipRoute(typeName: string): string{
    return `equipment/type/${typeName}`;
}

export function getEquipRegisterRoute(typeName: string): string{
    return `equipment/register/${typeName}`;
}
