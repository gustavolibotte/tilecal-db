export function preventEnterDefault(e: any){
    e.key === 'Enter' && e.preventDefault();
}