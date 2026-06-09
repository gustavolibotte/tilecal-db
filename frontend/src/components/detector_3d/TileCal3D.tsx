import { useState, useRef, useEffect, useMemo } from 'react';
import { Canvas, useFrame, useThree } from '@react-three/fiber';
import { OrbitControls, Grid, Edges, Billboard, Text } from '@react-three/drei';
import { useNavigate } from 'react-router-dom';
import * as THREE from 'three';
import { ModuleCells3D } from './ModuleCells3D';
import DbClient from '../../utils/DbClient';

const printStyles = `
  @media print {
    body * { visibility: hidden; }
    #cell-diagnostic-report, #cell-diagnostic-report * { visibility: visible; }
    #cell-diagnostic-report { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none !important; }
    .no-print { display: none !important; }
  }
`;

const BARRELS = [
  { id: 'EBA', zOffset: -3.35, label: 'EXTENDED BARREL A' },
  { id: 'LBA', zOffset: -1.05, label: 'LONG BARREL A' },
  { id: 'LBC', zOffset:  1.05, label: 'LONG BARREL C' },
  { id: 'EBC', zOffset:  3.35, label: 'EXTENDED BARREL C' },
];

export const TILECAL_OPTICS_MAPPING: Record<string, number[]> = {
  'A1': [1, 4], 'BC1': [2, 3], 'A2': [5, 8], 'BC2': [6, 7], 
  'A3': [9, 12], 'BC3': [10, 11], 'D0': [13, 14], 'D1': [15, 16],
  'A4': [15, 18], 'BC4': [17, 18], 'A5': [19, 20], 'BC5': [21, 22],
  'A6': [23, 24], 'BC6': [25, 26], 'D2': [27, 28], 'A7': [25, 28],
  'BC7': [29, 30], 'A8': [29, 32], 'BC8': [33, 34], 'A9': [33, 36],
  'BC9': [37, 38], 'A10': [39, 40], 'D3': [41, 42]
};

const getStatusColor = (statusName: string) => {
  const s = statusName?.toUpperCase() || '';
  if (['INSTALLED', 'TESTED', 'DELIVERED', 'NOMINAL', 'GOOD', 'OK', 'WORKING'].includes(s)) return '#4ade80'; 
  if (['TESTING', 'DEVELOPMENT', 'PREPRODUCTION', 'PRODUCED', 'REPAIRED', 'UNKNOWN', 'SPARE', 'WARNING'].includes(s)) return '#fbbf24'; 
  if (['FAILURE', 'BROKEN', 'LOST', 'ALERT'].includes(s)) return '#f87171'; 
  return '#9ca3af'; 
};

function CameraManager({ selectedModule, controlsRef }: { selectedModule: any, controlsRef: any }) {
  const { camera } = useThree();
  const macroState = useRef({ position: new THREE.Vector3(22, 12, 22), target: new THREE.Vector3(0, 0, 0) });

  useEffect(() => {
    if (!controlsRef.current) return;
    const controls = controlsRef.current;
    if (selectedModule) {
      macroState.current.position.copy(camera.position);
      macroState.current.target.copy(controls.target);
      camera.position.set(0, 0, 48);
      controls.target.set(0, 0, 0);
    } else {
      camera.position.copy(macroState.current.position);
      controls.target.copy(macroState.current.target);
    }
    controls.update();
  }, [selectedModule, camera, controlsRef]);
  return null;
}

function IndustrialSlice({ barrelId, sliceIndex, thetaStart, thetaLength, zOffset, setHoverData, diagnosticMode, moduleData, onModuleClick, onContextMenu }: any) {
  const meshRef = useRef<THREE.Mesh>(null!);
  const [hovered, setHover] = useState(false);
  
  const defaultColor = sliceIndex % 2 === 0 ? '#b3e5fc' : '#e1f5fe';
  let color = hovered ? '#ffbb00' : defaultColor; 

  if (diagnosticMode && !hovered) color = getStatusColor(moduleData?.status);

  useFrame(() => {
    if (!meshRef.current) return;
    const scale = THREE.MathUtils.lerp(meshRef.current.scale.x, hovered ? 1.04 : 1, 0.2);
    meshRef.current.scale.set(scale, scale, 1);
  });

  return (
    <mesh
      ref={meshRef} position={[0, zOffset, 0]} rotation={[0, thetaStart, 0]}
      onPointerOver={(e) => { e.stopPropagation(); setHover(true); setHoverData({ barrelId, sliceIndex, ...moduleData }); }}
      onPointerOut={(e) => { e.stopPropagation(); setHover(false); setHoverData(null); }}
      onClick={(e) => { e.stopPropagation(); onModuleClick(barrelId, String(sliceIndex + 1).padStart(2, '0')); }}
      onContextMenu={(e: any) => {
        e.stopPropagation();
        if (e.nativeEvent && typeof e.nativeEvent.preventDefault === 'function') {
          e.nativeEvent.preventDefault();
        }
        onContextMenu(e, barrelId, String(sliceIndex + 1).padStart(2, '0'));
      }}
    >
      <cylinderGeometry args={[2.7, 2.7, 2, 1, 1, false, 0, thetaLength]} />
      <meshStandardMaterial color={color} side={THREE.DoubleSide} roughness={0.1} metalness={0.2} transparent={true} opacity={hovered ? 0.9 : (diagnosticMode ? 0.8 : 0.65)} />
      <Edges linewidth={1} threshold={15} color="#546e7a" />
    </mesh>
  );
}

function BarrelAssembly({ id, zOffset, setHoverData, diagnosticMode, mockData, onModuleClick, onContextMenu }: any) {
  const slices = 64;
  const thetaLength = (Math.PI * 2) / slices;
  return (
    <group rotation={[Math.PI / 2, 0, 0]}>
      {Array.from({ length: slices }).map((_, i) => (
        <group key={i}>
          <IndustrialSlice barrelId={id} sliceIndex={i} thetaStart={i * thetaLength} thetaLength={thetaLength} zOffset={zOffset} setHoverData={setHoverData} diagnosticMode={diagnosticMode} moduleData={mockData?.[id]?.[i]} onModuleClick={onModuleClick} onContextMenu={onContextMenu} />
          {i % 8 === 0 && (
            <Billboard position={[Math.sin(i * thetaLength + thetaLength / 2) * 3.2, zOffset, Math.cos(i * thetaLength + thetaLength / 2) * 3.2]}>
              <Text fontSize={0.18} color="#0277bd" fontWeight="bold">{String(i + 1).padStart(2, '0')}</Text>
            </Billboard>
          )}
        </group>
      ))}
      <Billboard position={[4.0, zOffset, 2.0]}><Text fontSize={0.35} color="#1e88e5" fontWeight="bold" anchorX="center" anchorY="bottom">{id}</Text></Billboard>
    </group>
  );
}

export default function TileCal3D() {
  const navigate = useNavigate();
  const [hoverData, setHoverData] = useState<any>(null);
  const [diagnosticMode, setDiagnosticMode] = useState(false);
  const [selectedModule, setSelectedModule] = useState<{ barrel: string; number: string } | null>(null);
  
  const [contextMenu, setContextMenu] = useState<{ x: number, y: number, barrel: string, number: string } | null>(null);
  const [isTreeModalOpen, setIsTreeModalOpen] = useState(false);
  const [modalModule, setModalModule] = useState<{ barrel: string, number: string } | null>(null);
  const [treeData, setTreeData] = useState<any[] | null>(null);

  const [selectedCell, setSelectedCell] = useState<string | null>(null);

  const [detectorData, setDetectorData] = useState<Record<string, any[]> | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const controlsRef = useRef<any>(null);

  useEffect(() => {
    const handleClickOutside = () => setContextMenu(null);
    document.addEventListener('click', handleClickOutside);
    return () => document.removeEventListener('click', handleClickOutside);
  }, []);

  useEffect(() => {
    const loadGlobalData = async () => {
      try {
        setIsLoading(true);
        const response = await DbClient.get('api/calorimeter/status');
        setDetectorData(response.data?.data || response.data);
      } catch (e: any) {
        console.error("Failed to load global DB data:", e);
        setDetectorData({}); 
      } finally {
        setIsLoading(false);
      }
    };
    loadGlobalData();
  }, []);

  useEffect(() => {
    const target = selectedModule || (isTreeModalOpen ? modalModule : null);
    if (target) {
      if (!selectedModule) setTreeData(null); 
      DbClient.get(`api/calorimeter/module/${target.barrel}${target.number}`)
        .then(response => { setTreeData(response.data?.data || response.data); })
        .catch(e => { console.error("Failed to load hierarchical tree:", e); setTreeData([]); });
    }
  }, [selectedModule, isTreeModalOpen, modalModule]);

  const cellStatuses = useMemo(() => {
    if (!treeData) return {};
    const statuses: Record<string, string> = {};
    
    Object.keys(TILECAL_OPTICS_MAPPING).forEach(cell => {
      const channels = TILECAL_OPTICS_MAPPING[cell];
      let errors = 0; let warnings = 0; let nominals = 0; let found = 0;

      channels.forEach(ch => {
        let blockFound: any = null;
        treeData.forEach(d => { d.blocks?.forEach((b: any) => { if (Number(b.channel) === ch) blockFound = b; }); });
        
        if (blockFound) {
          found++;
          const allComps = [blockFound, ...(blockFound.components || [])];
          allComps.forEach(comp => {
            const s = comp.status?.toUpperCase() || '';
            if (['FAILURE', 'BROKEN', 'LOST', 'ALERT'].includes(s)) errors++;
            else if (['INSTALLED', 'TESTED', 'DELIVERED', 'NOMINAL', 'GOOD', 'OK', 'WORKING'].includes(s)) nominals++;
            else warnings++; 
          });
        }
      });

      if (found === 0) statuses[cell] = 'EMPTY';
      else if (errors > 0) statuses[cell] = 'ALERT';
      else if (warnings > 0) statuses[cell] = 'WARNING';
      else statuses[cell] = 'NOMINAL';
    });
    return statuses;
  }, [treeData]);

  const handleModuleClick = (barrel: string, moduleNumber: string) => { setSelectedModule({ barrel, number: moduleNumber }); setHoverData(null); };
  const handleBackToGlobal = () => { setSelectedModule(null); setHoverData(null); setSelectedCell(null); };

  const handleRightClick = (e: any, barrel: string, number: string) => {
    setContextMenu({ x: e.clientX, y: e.clientY, barrel, number });
  };

  const openTreeModal = () => {
    if (contextMenu) {
      setModalModule({ barrel: contextMenu.barrel, number: contextMenu.number });
      setIsTreeModalOpen(true);
      setContextMenu(null);
    }
  };

  const handleCellClick = (cellId: string) => {
    setSelectedCell(cellId);
    setHoverData(null);
  };

  const triggerPrint = () => {
    window.print();
  };

  if (isLoading || !detectorData) {
    return (
      <div style={{ height: 'calc(100vh - 100px)' }} className="w-full flex items-center justify-center bg-gray-100 text-blue-900 font-bold font-mono border-round">
        <i className="pi pi-spin pi-spinner mr-3" style={{ fontSize: '2rem' }}></i>
        FETCHING TILECAL MOUNTING STATISTICS...
      </div>
    );
  }

  const renderModuleOverview = () => {
    const stats = hoverData?.stats || {};
    const types = Object.keys(stats);
    return (
      <div style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '10px', flexShrink: 0 }}>
          <span style={{ fontSize: '18px', fontWeight: 'bold', color: '#000' }}>
            {hoverData.barrelId}-{String(hoverData.sliceIndex + 1).padStart(2, '0')}
          </span>
          <span style={{ color: getStatusColor(hoverData.status), fontWeight: 'bold', fontSize: '16px' }}>
            {hoverData.status}
          </span>
        </div>
        
        <div style={{ marginTop: '5px', paddingTop: '10px', borderTop: '1px dashed #b3e5fc', display: 'flex', flexDirection: 'column', flexGrow: 1, minHeight: 0 }}>
          <b style={{color: '#000', fontSize: '12px', flexShrink: 0, paddingBottom: '10px'}}>MOUNTING STATISTICS</b>
          
          <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', overflowY: 'auto', paddingRight: '5px', flexGrow: 1 }}>
            {types.length === 0 ? (
              <div style={{ color: '#9ca3af', fontStyle: 'italic' }}>No components registered.</div>
            ) : (
              types.map(type => {
                const statusCounts = stats[type];
                return (
                  <div key={type} style={{ fontSize: '11px', backgroundColor: '#f8fafc', padding: '6px', borderRadius: '4px' }}>
                    <div style={{ fontWeight: 'bold', color: '#546e7a', marginBottom: '4px' }}>{type}</div>
                    {Object.entries(statusCounts).map(([status, qty]) => (
                      <div key={status} style={{ display: 'flex', justifyContent: 'space-between' }}>
                        <span style={{ color: getStatusColor(status), fontWeight: 'bold' }}>■ {status}</span>
                        <b>{qty as number} units</b>
                      </div>
                    ))}
                  </div>
                );
              })
            )}
          </div>
        </div>
      </div>
    );
  };

  return (
    <div style={{ height: 'calc(100vh - 100px)', width: '100%', backgroundColor: '#ffffff', borderRadius: '8px', position: 'relative', overflow: 'hidden', boxShadow: '0 4px 10px rgba(0,0,0,0.05)' }}>
      <style dangerouslySetInnerHTML={{ __html: printStyles }} />

      {selectedCell && treeData && (
        <div className="no-print" style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', backgroundColor: 'rgba(0,0,0,0.6)', zIndex: 1000, display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
          <div id="cell-diagnostic-report" style={{ width: '90%', maxWidth: '850px', backgroundColor: '#fff', borderRadius: '8px', padding: '30px', boxShadow: '0 10px 25px rgba(0,0,0,0.3)', maxHeight: '90vh', overflowY: 'auto' }}>
            
            <div className="no-print" style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: '10px' }}>
              <button onClick={triggerPrint} style={{ padding: '8px 15px', backgroundColor: '#0288d1', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer', fontWeight: 'bold', marginRight: '10px' }}>
                <i className="pi pi-print mr-2"></i> Export Report PDF
              </button>
              <button onClick={() => setSelectedCell(null)} style={{ padding: '8px 15px', backgroundColor: '#f87171', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer', fontWeight: 'bold' }}>
                <i className="pi pi-times mr-2"></i> Close
              </button>
            </div>

            <div style={{ borderBottom: '3px solid #0277bd', paddingBottom: '15px', marginBottom: '20px' }}>
              <h2 style={{ margin: 0, color: '#01579b', fontSize: '24px' }}>CELL DIAGNOSTIC REPORT</h2>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: '10px', fontSize: '16px' }}>
                <span>Module: <b>{selectedModule?.barrel}-{selectedModule?.number}</b> | Cell ID: <b>{selectedCell}</b></span>
                <span>Cell Global Status: <b style={{ color: getStatusColor(cellStatuses[selectedCell]) }}>{cellStatuses[selectedCell] || 'EMPTY'}</b></span>
              </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '30px' }}>
              {(TILECAL_OPTICS_MAPPING[selectedCell] || []).map(ch => {
                let blockFound: any = null;
                treeData.forEach(d => { d.blocks?.forEach((b: any) => { if (Number(b.channel) === ch) blockFound = b; }); });
                
                const pmt = blockFound?.components?.find((c:any) => c.type === 'PMT');
                const fenics = blockFound?.components?.find((c:any) => c.type.includes('FENICS'));
                const divider = blockFound?.components?.find((c:any) => c.type.includes('Divider'));

                return (
                  <div key={ch} style={{ backgroundColor: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: '6px', padding: '20px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', borderBottom: '2px solid #b3e5fc', paddingBottom: '10px', marginBottom: '15px' }}>
                      <span style={{ fontSize: '18px', color: '#01579b', fontWeight: 'bold' }}>CHANNEL {String(ch).padStart(2, '0')}</span>
                      <span style={{ color: getStatusColor(blockFound?.status), fontWeight: 'bold' }}>{blockFound ? blockFound.status : 'EMPTY'}</span>
                    </div>

                    {blockFound ? (
                      <div>
                        <div style={{ fontWeight: 'bold', fontSize: '14px', marginBottom: '10px', color: '#334155' }}>
                          PMT Block (S/N: {blockFound.id !== 'N/A' ? blockFound.id : 'Orphaned'})
                        </div>
                        <ul style={{ paddingLeft: '20px', margin: 0, listStyleType: 'square', lineHeight: '1.8', color: '#475569' }}>
                          <li>
                            <b>PMT:</b> {pmt ? (
                              <span><span style={{color: '#0288d1', cursor: 'pointer'}} onClick={() => { setSelectedCell(null); navigate(`/equipment/${pmt.id}`); }}>{pmt.id}</span> | {pmt.model} | QE: {pmt.qe}% | HV: {pmt.hv}V - <b style={{color: getStatusColor(pmt.status)}}>{pmt.status}</b></span>
                            ) : <i style={{color: '#94a3b8'}}>Missing from DB</i>}
                          </li>
                          <li>
                            <b>FENICS:</b> {fenics ? (
                              <span><span style={{color: '#0288d1', cursor: 'pointer'}} onClick={() => { setSelectedCell(null); navigate(`/equipment/${fenics.id}`); }}>{fenics.id}</span> - <b style={{color: getStatusColor(fenics.status)}}>{fenics.status}</b></span>
                            ) : <i style={{color: '#94a3b8'}}>Missing from DB</i>}
                          </li>
                          <li>
                            <b>HV Divider:</b> {divider ? (
                              <span><span style={{color: '#0288d1', cursor: 'pointer'}} onClick={() => { setSelectedCell(null); navigate(`/equipment/${divider.id}`); }}>{divider.id}</span> - <b style={{color: getStatusColor(divider.status)}}>{divider.status}</b></span>
                            ) : <i style={{color: '#94a3b8'}}>Missing from DB</i>}
                          </li>
                        </ul>
                      </div>
                    ) : (
                      <div style={{ textAlign: 'center', padding: '30px 0', color: '#9ca3af', fontStyle: 'italic' }}>
                        No read-out components mapped to this physical channel.
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
            
            <div style={{ marginTop: '30px', borderTop: '1px dashed #cbd5e1', paddingTop: '15px', fontSize: '12px', color: '#64748b', textAlign: 'center' }}>
              Report automatically generated by ATLAS TileCal Parameter DB • Date: {new Date().toLocaleString()}
            </div>
          </div>
        </div>
      )}

      {/* CONTEXT MENU */}
      {contextMenu && (
        <div className="no-print" style={{ position: 'fixed', left: contextMenu.x, top: contextMenu.y, backgroundColor: '#fff', border: '1px solid #ccc', borderRadius: '4px', padding: '5px 0', zIndex: 200, boxShadow: '0 2px 10px rgba(0,0,0,0.2)', width: '180px' }}>
          <div style={{ padding: '8px 15px', color: '#546e7a', fontWeight: 'bold', borderBottom: '1px solid #f0f0f0', fontSize: '12px' }}>
            {contextMenu.barrel}-{contextMenu.number} OPTIONS
          </div>
          <div onClick={openTreeModal} style={{ padding: '10px 15px', cursor: 'pointer', fontSize: '13px', color: '#01579b' }}>
            <i className="pi pi-sitemap mr-2"></i> View Sub-Components Tree
          </div>
        </div>
      )}

      <div className="no-print" style={{ position: 'absolute', top: '15px', left: '15px', color: '#01579b', fontFamily: 'monospace', fontSize: '11px', zIndex: 1, background: 'rgba(255,255,255,0.9)', padding: '15px', border: '1px solid #b3e5fc', borderRadius: '6px', pointerEvents: 'none' }}>
        <b style={{ fontSize: '13px', color: '#000' }}>ATLAS TILECAL SYS</b><br/><br/>
        {selectedModule ? (
          <div style={{ pointerEvents: 'auto' }}>
            VIEW: <span style={{ color: '#0277bd', fontWeight: 'bold' }}>CELL DIAGNOSTIC</span><br/>
            TARGET: {selectedModule.barrel}-{selectedModule.number}<br/><br/>
            <button onClick={handleBackToGlobal} style={{ width: '100%', padding: '8px', backgroundColor: '#0288d1', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer', fontWeight: 'bold' }}>
              <i className="pi pi-arrow-left" style={{ marginRight: '6px' }}></i> BACK TO GLOBAL
            </button>
          </div>
        ) : (
          <div style={{ pointerEvents: 'auto' }}>
            DATA SYNC: API CONNECTED<br/><br/>
            <div style={{ display: 'flex', alignItems: 'center', cursor: 'pointer', borderTop: '1px dashed #b3e5fc', paddingTop: '10px' }} onClick={() => setDiagnosticMode(!diagnosticMode)}>
              <div style={{ width: '36px', height: '18px', backgroundColor: diagnosticMode ? '#43a047' : '#cfd8dc', borderRadius: '18px', position: 'relative', marginRight: '10px' }}>
                <div style={{ width: '14px', height: '14px', backgroundColor: '#fff', borderRadius: '50%', position: 'absolute', top: '2px', left: diagnosticMode ? '20px' : '2px' }}/>
              </div>
              <b style={{ color: diagnosticMode ? '#000' : '#78909c' }}>GLOBAL DIAGNOSTIC</b>
            </div>
          </div>
        )}
      </div>

      {selectedModule && hoverData && hoverData.clientX && !selectedCell && !isTreeModalOpen && (
        <div className="no-print" style={{ position: 'fixed', left: hoverData.clientX + 15, top: hoverData.clientY + 15, backgroundColor: 'rgba(0, 0, 0, 0.8)', color: '#ffffff', padding: '4px 10px', borderRadius: '4px', fontFamily: 'monospace', fontSize: '14px', fontWeight: 'bold', pointerEvents: 'none', zIndex: 100 }}>
          CELL: {hoverData.id}
        </div>
      )}

      {!selectedModule && hoverData && !isTreeModalOpen && !contextMenu && (
        <div className="no-print" style={{ position: 'absolute', bottom: '20px', right: '20px', backgroundColor: 'rgba(255, 255, 255, 0.95)', color: '#01579b', padding: '20px', borderRadius: '8px', fontFamily: 'monospace', fontSize: '13px', border: '1px solid #0277bd', width: '320px', pointerEvents: 'auto', boxShadow: '0 4px 15px rgba(0,0,0,0.1)', display: 'flex', flexDirection: 'column', maxHeight: 'calc(100vh - 80px)' }}>
          <div style={{ borderBottom: '1px solid #0277bd', paddingBottom: '10px', marginBottom: '15px', fontSize: '16px', fontWeight: 'bold', flexShrink: 0 }}>
            MODULE OVERVIEW
          </div>
          {renderModuleOverview()}
        </div>
      )}

      {/* CANVAS 3D */}
      <Canvas className="no-print" camera={{ position: [22, 12, 22], fov: 20 }} style={{ width: '100%', height: '100%', display: 'block' }}>
        <CameraManager selectedModule={selectedModule} controlsRef={controlsRef} />
        <ambientLight intensity={1.5} />
        <directionalLight position={[10, 15, 10]} intensity={2} color="#ffffff" />
        <directionalLight position={[-10, -10, -10]} intensity={0.5} color="#90caf9" />

        {!selectedModule && <Grid position={[0, -3.5, 0]} args={[40, 40]} sectionColor="#e0e0e0" cellColor="#f5f5f5" infiniteGrid />}
        {selectedModule ? (
          <group scale={0.08} position={[0, -1.5, 0]}>
            <ModuleCells3D 
              barrel={selectedModule.barrel} 
              moduleNumber={selectedModule.number} 
              onHoverCell={setHoverData}
              onClickCell={handleCellClick}
              diagnosticMode={diagnosticMode}
              cellStatuses={cellStatuses}
            />
          </group>
        ) : (
          <group>
            {BARRELS.map(b => <BarrelAssembly key={b.id} id={b.id} zOffset={b.zOffset} setHoverData={setHoverData} diagnosticMode={diagnosticMode} mockData={detectorData} onModuleClick={handleModuleClick} onContextMenu={handleRightClick} />)}
          </group>
        )}
        <OrbitControls ref={controlsRef} enablePan={true} enableZoom={true} enableRotate={!selectedModule} />
      </Canvas>
    </div>
  );
}