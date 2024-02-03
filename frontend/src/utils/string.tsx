export function snakeToSpaced(str: string): string{
    let words = str.split('_');
    words = words.map((word)=>{ return capitalize(word); });
    const newString = words.join(' '); 
    return newString;
}

export function capitalize(str: string): string{
    return str.charAt(0).toUpperCase()+str.slice(1);
}

export function makeSnakeCase(str: string): string{
    return str.toLowerCase().replace(' ', '_');
}