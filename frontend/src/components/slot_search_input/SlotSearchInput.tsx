import { useState } from 'react';
import { InputText } from 'primereact/inputtext';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { useQuery } from '@tanstack/react-query';
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

    const queryParams: any = {
        id: processSearchTerm(searchTerm),
        id_matchMode: 'contains',
    };

    if (allowedTypes && allowedTypes.length > 0) {
        queryParams.type_name = allowedTypes;
    }

    const { data, isFetching } = useQuery({
        queryKey: ['equipment', 'get', queryParams],
        queryFn: defaultQueryFunction,
        enabled: isModalOpen 
    });

    const responseData: any = data?.data || [];
    const results = (!Array.isArray(responseData) && responseData.data !== undefined) 
        ? responseData.data 
        : responseData;

    const onRowSelect = (e: any) => {
        onChange(e.data.id);
        setSearchTerm(e.data.id);
        setIsModalOpen(false);
    };

    const handleSearchClick = () => {
        setIsModalOpen(true);
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
                            handleSearchClick();
                        }
                    }}
                    placeholder="Type partial ID and search..."
                    className={errorText ? 'p-invalid' : ''}
                />
                <Button icon="pi pi-search" type="button" onClick={handleSearchClick} />
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
                    loading={isFetching} 
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