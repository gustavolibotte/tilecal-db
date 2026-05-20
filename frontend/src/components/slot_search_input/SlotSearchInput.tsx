import { useState } from 'react';
import { InputText } from 'primereact/inputtext';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { useQueryClient } from '@tanstack/react-query';
import { defaultQueryFunction } from '../../utils/DbClient';

interface SlotSearchInputProps {
    id: string;
    label: string;
    value: string;
    onChange: (val: string) => void;
    allowedTypes?: string[]; 
    errorText?: string | null;
    required?: boolean;
    help?: string;
}

const processSearchTerm = (term: string) => {
    if (!term) return term;
    
    const chunks = term.split(/[^a-zA-Z0-9]+/);
    const validChunks = chunks.filter(c => c.length >= 3);
    
    if (validChunks.length > 1) {
        return validChunks;
    }
    
    return term;
};

export default function SlotSearchInput({ id, label, value, onChange, allowedTypes, errorText, required, help }: SlotSearchInputProps) {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState(value);
    const [results, setResults] = useState<any[]>([]);
    const [isSearching, setIsSearching] = useState(false);
    
    const queryClient = useQueryClient();

    const focusNextField = () => {
        const currentInput = document.getElementById(id);
        if (currentInput) {
            const form = currentInput.closest('form');
            if (form) {
                const interactables = Array.from(
                    form.querySelectorAll('input:not([disabled]), button[type="submit"]:not([disabled])')
                ) as HTMLElement[];
                const currentIndex = interactables.indexOf(currentInput);
                if (currentIndex > -1 && currentIndex < interactables.length - 1) {
                    interactables[currentIndex + 1].focus();
                }
            }
        }
    };

    const performSearch = async (isBackground: boolean, termToSearch: string) => {
        if (!termToSearch) return;
        setIsSearching(true);
        
        try {
            const queryParams: any = {
                id: processSearchTerm(termToSearch),
                id_matchMode: 'contains',
            };

            if (allowedTypes && allowedTypes.length > 0) {
                queryParams.type_name = allowedTypes;
            }

            const response = await queryClient.fetchQuery({
                queryKey: ['equipment', 'get', queryParams],
                queryFn: defaultQueryFunction
            });

            const responseData: any = response?.data || [];
            const fetchedResults = (!Array.isArray(responseData) && responseData.data !== undefined) 
                ? responseData.data 
                : responseData;

            setResults(fetchedResults);

            if (isBackground && fetchedResults.length === 1) {
                const exactMatchId = fetchedResults[0].id;
                onChange(exactMatchId);
                setSearchTerm(exactMatchId);
                focusNextField();
            } else {
                setIsModalOpen(true);
            }
        } catch (error) {
            console.error("Erro na busca de montagem:", error);
        } finally {
            setIsSearching(false);
        }
    };

    const onRowSelect = (e: any) => {
        onChange(e.data.id);
        setSearchTerm(e.data.id);
        setIsModalOpen(false);
        focusNextField();
    };

    const handleSearchClick = () => {
        performSearch(false, searchTerm);
    };

    return (
        <div className="field">
            <label htmlFor={id} className="font-bold">
                {label}{required && '*'}:
            </label>
            <div className="p-inputgroup mt-1">
                <InputText
                    id={id}
                    value={value}
                    onChange={(e) => {
                        onChange(e.target.value);
                        setSearchTerm(e.target.value);
                    }}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            performSearch(true, e.currentTarget.value);
                        }
                    }}
                    placeholder="Type partial ID and search..."
                    className={errorText ? 'p-invalid' : ''}
                />
                <Button 
                    icon="pi pi-search" 
                    type="button" 
                    onClick={handleSearchClick} 
                    loading={isSearching} 
                />
            </div>
            {help && <small className="block mt-1 text-500">{help}</small>}
            {errorText && <small className="p-error block mt-1">{errorText}</small>}

            <Dialog 
                header={`Select Equipment for: ${label}`} 
                visible={isModalOpen} 
                style={{ width: '70vw' }} 
                onHide={() => setIsModalOpen(false)}
                dismissableMask
            >
                <div className="mb-3 text-600">
                    <i className="pi pi-info-circle mr-2"></i>
                    Found the following matches for your search. Click on a row to select the exact ID.
                </div>
                <DataTable 
                    value={results} 
                    loading={isSearching} 
                    selectionMode="single" 
                    onRowSelect={onRowSelect} 
                    emptyMessage="No equipments found matching your search." 
                    paginator 
                    rows={5}
                    stripedRows
                    showGridlines
                >
                    <Column field="id" header="ID" />
                    <Column field="type_name" header="Type" />
                    <Column field="status_name" header="Status" />
                    <Column field="location_name" header="Location" />
                    <Column field="owner_name" header="Owner" />
                </DataTable>
            </Dialog>
        </div>
    );
}