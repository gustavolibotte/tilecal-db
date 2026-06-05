import { useState, useMemo, useRef } from 'react';
import { useQuery, useMutation } from '@tanstack/react-query';
import { DataTable, DataTableRowData } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Dropdown } from 'primereact/dropdown';
import { Button } from 'primereact/button';
import { toast, Id } from 'react-toastify';
import { getEndpoint, makeMutationFunction, defaultQueryFunction } from '../../utils/DbClient';
import { Message } from 'primereact/message';
import { Card } from 'primereact/card';

// Interfaces
interface ChildEquip {
  id: string;
  type_name: string;
  location_name: string;
  slot_name: string;
}

interface ParentEquip {
  id: string;
  type_name: string;
  location_name: string;
}

interface InconsistencyData {
  parent: ParentEquip;
  children: ChildEquip[];
}

export default function PmtConsistency() {
  const [expandedRows, setExpandedRows] = useState<any[] | DataTableRowData<any[]> | null>(null);
  
  const [pendingUpdates, setPendingUpdates] = useState<Record<string, string>>({});
  const toastId = useRef<Id>();

  const locationsQuery: any = useQuery({ queryKey: ['location', 'get', {}], queryFn: defaultQueryFunction });
  const locations = useMemo(() => locationsQuery.data?.data?.map((l: any) => l.name) || [], [locationsQuery.data]);

  const inconsistenciesQuery: any = useQuery({
    queryKey: ['equipment_inconsistencies', 'get', {}],
    queryFn: defaultQueryFunction
  });

  const dataset: InconsistencyData[] = inconsistenciesQuery.data?.data || [];

  const saveMutation = useMutation({
    mutationFn: makeMutationFunction('post', getEndpoint('equipment') + '/batch-update-locations'),
    onMutate: () => { toastId.current = toast.loading('Applying location fixes...'); },
    onSuccess: () => {
      toast.update(toastId.current!, { render: "Inconsistencies fixed successfully!", type: "success", isLoading: false, autoClose: 3000 });
      setPendingUpdates({});
      inconsistenciesQuery.refetch(); 
    },
    onError: (err: any) => {
      toast.update(toastId.current!, { render: `Error: ${err.message}`, type: "error", isLoading: false, autoClose: 5000 });
    }
  });

  const totalInconsistentBlocks = dataset.length;
  const totalAffectedChildren = dataset.reduce((acc, curr) => acc + curr.children.length, 0);
  const pendingChangesCount = Object.keys(pendingUpdates).length;

  const handleLocationChange = (equipId: string, newLocation: string) => {
    setPendingUpdates(prev => ({ ...prev, [equipId]: newLocation }));
  };

  const syncChildrenToParent = (rowData: InconsistencyData) => {
    const parentLoc = pendingUpdates[rowData.parent.id] || rowData.parent.location_name;
    const newUpdates = { ...pendingUpdates };
    
    rowData.children.forEach(child => {
      newUpdates[child.id] = parentLoc;
    });
    setPendingUpdates(newUpdates);
    toast.info(`Synced ${rowData.children.length} components to ${parentLoc}`);
  };

  const syncAllToTheirParents = () => {
    const newUpdates = { ...pendingUpdates };
    dataset.forEach(row => {
      const parentLoc = newUpdates[row.parent.id] || row.parent.location_name;
      row.children.forEach(child => {
        newUpdates[child.id] = parentLoc;
      });
    });
    setPendingUpdates(newUpdates);
    toast.success("Global Sync applied. Please review and commit changes.");
  };

  const commitChanges = () => {
    const updatesArray = Object.keys(pendingUpdates).map(id => ({
      id,
      location_name: pendingUpdates[id]
    }));
    if (updatesArray.length > 0) {
      saveMutation.mutate({ updates: updatesArray });
    }
  };

  const locationDropdownTemplate = (id: string, currentLoc: string) => {
    const isModified = !!pendingUpdates[id] && pendingUpdates[id] !== currentLoc;
    const value = pendingUpdates[id] || currentLoc;

    return (
      <div className="flex align-items-center gap-2">
        <Dropdown 
          value={value} 
          options={locations} 
          onChange={(e) => handleLocationChange(id, e.value)} 
          className={`w-full md:w-14rem ${isModified ? 'border-orange-500 bg-orange-50' : ''}`}
          placeholder="Select Location" 
        />
        {isModified && <i className="pi pi-exclamation-circle text-orange-500" title="Modified. Pending commit." />}
      </div>
    );
  };

  const rowExpansionTemplate = (data: InconsistencyData) => {
    return (
      <div className="p-3 surface-100 border-round">
        <div className="flex justify-content-between align-items-center mb-2">
          <h5 className="m-0 text-700"><i className="pi pi-sitemap mr-2"></i>Assembled Components inside {data.parent.id}</h5>
          <Button label="Sync all to PMT Block's Location" icon="pi pi-sync" size="small" outlined onClick={() => syncChildrenToParent(data)} />
        </div>
        <DataTable value={data.children} size="small" responsiveLayout="scroll" className="shadow-1">
          <Column field="id" header="Component ID" style={{ fontWeight: 'bold' }}></Column>
          <Column field="type_name" header="Type"></Column>
          <Column field="slot_name" header="Mounted In Slot"></Column>
          <Column header="Current Database Location" body={(d) => (
             <span className={d.location_name !== data.parent.location_name ? 'text-red-600 font-bold' : 'text-green-600'}>
               <i className={`pi ${d.location_name !== data.parent.location_name ? 'pi-times' : 'pi-check'} mr-1`}></i>
               {d.location_name || 'NULL'}
             </span>
          )}></Column>
          <Column header="New Target Location" body={(d) => locationDropdownTemplate(d.id, d.location_name)}></Column>
        </DataTable>
      </div>
    );
  };

  if (inconsistenciesQuery.isPending || locationsQuery.isPending) return <p>Scanning database for inconsistencies...</p>;
  
  if (inconsistenciesQuery.isError) {
    return <Message severity="error" text="Failed to fetch consistency data. Internal Server Error." className="w-full" />;
  }

  return (
    <div className="flex flex-column gap-4">
      <div className="flex justify-content-between align-items-center">
        <div>
          <h2 className="m-0 text-900">PMT Block Location Consistency QC</h2>
          <p className="text-500 mt-1">Identify and resolve location mismatches between PMT Blocks and their assembled sub-components.</p>
        </div>
      </div>

      <div className="grid">
        <div className="col-12 md:col-4">
          <Card className="shadow-2 border-left-3 border-blue-500">
            <div className="flex justify-content-between">
              <div>
                <span className="block text-500 font-medium mb-3">Inconsistent PMT Blocks</span>
                <div className="text-900 font-bold text-xl">{totalInconsistentBlocks} blocks</div>
              </div>
              <div className="flex align-items-center justify-content-center bg-blue-100 border-round" style={{ width: '2.5rem', height: '2.5rem' }}>
                <i className="pi pi-box text-blue-500 text-xl"></i>
              </div>
            </div>
          </Card>
        </div>
        <div className="col-12 md:col-4">
          <Card className="shadow-2 border-left-3 border-orange-500">
            <div className="flex justify-content-between">
              <div>
                <span className="block text-500 font-medium mb-3">Assembled Components Involved</span>
                <div className="text-900 font-bold text-xl">{totalAffectedChildren} components</div>
              </div>
              <div className="flex align-items-center justify-content-center bg-orange-100 border-round" style={{ width: '2.5rem', height: '2.5rem' }}>
                <i className="pi pi-microchip text-orange-500 text-xl"></i>
              </div>
            </div>
          </Card>
        </div>
        <div className="col-12 md:col-4">
          <Card className="shadow-2 border-left-3 border-green-500 bg-green-50">
            <div className="flex justify-content-between">
              <div>
                <span className="block text-green-700 font-medium mb-3">Pending Fixes to Commit</span>
                <div className="text-green-900 font-bold text-xl">{pendingChangesCount} updates queued</div>
              </div>
              <div className="flex align-items-center justify-content-center bg-green-200 border-round" style={{ width: '2.5rem', height: '2.5rem' }}>
                <i className="pi pi-save text-green-700 text-xl"></i>
              </div>
            </div>
          </Card>
        </div>
      </div>

      {dataset.length === 0 ? (
        <Message severity="success" text="Great news! The database is perfectly consistent. No location mismatches found in assembled components." className="w-full" />
      ) : (
        <div className="card shadow-2 border-round">
          <div className="flex justify-content-between align-items-center mb-4 p-3 bg-gray-50 border-round">
             <Button label="Global Fix: Set ALL components to their Parent's Location" icon="pi pi-bolt" severity="secondary" onClick={syncAllToTheirParents} />
             <Button label={`Commit ${pendingChangesCount} Changes to DB`} icon="pi pi-database" severity="success" disabled={pendingChangesCount === 0} onClick={commitChanges} />
          </div>

          <DataTable 
            value={dataset} 
            expandedRows={expandedRows} 
            onRowToggle={(e) => setExpandedRows(e.data)}
            rowExpansionTemplate={rowExpansionTemplate} 
            dataKey="parent.id" 
            header="Expand rows to fix individual component locations"
            stripedRows
          >
            <Column expander style={{ width: '3em' }} />
            <Column field="parent.id" header="PMT Block ID" style={{ fontWeight: 'bold' }}></Column>
            <Column header="Current Block Location" body={(d) => d.parent.location_name}></Column>
            <Column header="Edit PMT Block Location" body={(d) => locationDropdownTemplate(d.parent.id, d.parent.location_name)}></Column>
            <Column header="Inconsistency Alert" body={(d) => (
                <span className="bg-red-100 text-red-800 px-2 py-1 border-round text-sm font-bold">
                   <i className="pi pi-exclamation-triangle mr-1"></i>
                   {d.children.length} components misplaced
                </span>
            )}></Column>
          </DataTable>
        </div>
      )}
    </div>
  );
}