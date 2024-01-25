export function getSpecEquipRoute(typeName: string): string{
    return `equipment/type/${typeName}`;
}

export function getEquipRegisterRoute(typeName: string): string{
    return `equipment/register/${typeName}`;
}

export function getEquipviewRoute(id: string): string{
    return `equipment/${id}`;
}
