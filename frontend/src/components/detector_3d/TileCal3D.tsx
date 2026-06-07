import { useState, useRef, useEffect } from 'react';
import { Canvas, useFrame, useThree } from '@react-three/fiber';
import { OrbitControls, Grid, Edges, Billboard, Text } from '@react-three/drei';
import { useNavigate } from 'react-router-dom';
import * as THREE from 'three';
import { ModuleCells3D } from './ModuleCells3D';
import DbClient from '../../utils/DbClient';

const BARRELS = [
  { id: 'EBA', zOffset: -3.35, label: 'EXTENDED BARREL A' },
  { id: 'LBA', zOffset: -1.05, label: 'LONG BARREL A' },
  { id: 'LBC', zOffset:  1.05, label: 'LONG BARREL C' },
  { id: 'EBC', zOffset:  3.35, label: 'EXTENDED BARREL C' },
];

const TILECAL_OPTICS_MAPPING: Record<string, number[]> = {
  'A1': [1, 4], 'A2': [5, 8], 'A3': [9, 12],
  'B1': [2, 3], 'B2': [6, 7], 'D0': [13, 14],
};

const getStatusColor = (statusName: string) => {
  const s = statusName?.toUpperCase() || '';
  if (['INSTALLED', 'TESTED', 'DELIVERED', 'NOMINAL', 'GOOD', 'OK', 'WORKING'].includes(s)) return '#4ade80'; // Green
  if (['TESTING', 'DEVELOPMENT', 'PREPRODUCTION', 'PRODUCED', 'REPAIRED', 'UNKNOWN', 'SPARE', 'WARNING'].includes(s)) return '#fbbf24'; // Yellow
  if (['FAILURE', 'BROKEN', 'LOST', 'ALERT'].includes(s)) return '#f87171'; // Red
  return '#9ca3af'; // Gray
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
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalModule, setModalModule] = useState<{ barrel: string, number: string } | null>(null);
  const [modalPmtData, setModalPmtData] = useState<Record<string, any> | null>(null);

  const [detectorData, setDetectorData] = useState<Record<string, any[]> | null>(null);
  const [pmtData, setPmtData] = useState<Record<string, any>>({}); 
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
      } catch (e) {
        console.error("Failed to load global DB data:", e);
        setDetectorData({}); 
      } finally {
        setIsLoading(false);
      }
    };
    loadGlobalData();
  }, []);

  useEffect(() => {
    if (selectedModule) {
      setPmtData({}); 
      DbClient.get(`api/calorimeter/module/${selectedModule.barrel}${selectedModule.number}`)
        .then(response => { setPmtData(response.data?.data || response.data); })
        .catch(e => console.error("Failed to load PMT data:", e));
    }
  }, [selectedModule]);

  useEffect(() => {
    if (isModalOpen && modalModule) {
      setModalPmtData(null); 
      DbClient.get(`api/calorimeter/module/${modalModule.barrel}${modalModule.number}`)
        .then(response => { setModalPmtData(response.data?.data || response.data); })
        .catch(e => { console.error("Failed to load modal PMT data:", e); setModalPmtData({}); });
    }
  }, [isModalOpen, modalModule]);

  const handleModuleClick = (barrel: string, moduleNumber: string) => { setSelectedModule({ barrel, number: moduleNumber }); setHoverData(null); };
  const handleBackToGlobal = () => { setSelectedModule(null); setHoverData(null); };

  const handleRightClick = (e: any, barrel: string, number: string) => {
    setContextMenu({ x: e.clientX, y: e.clientY, barrel, number });
  };

  const openDetailsModal = () => {
    if (contextMenu) {
      setModalModule({ barrel: contextMenu.barrel, number: contextMenu.number });
      setIsModalOpen(true);
      setContextMenu(null);
    }
  };

  const exportToCSV = () => {
    if (!modalPmtData) return;
    const rows = [['Channel', 'PMT_Serial', 'PMT_Model', 'PMT_QE', 'PMT_HV', 'PMT_Status', 'Block_Serial', 'Block_Status', 'Fenics_Serial', 'Fenics_Status', 'Divider_Serial', 'Divider_Status']];
    for (let ch = 1; ch <= 48; ch++) {
      const c = modalPmtData[ch] || {};
      rows.push([
        ch, c.pmt?.serial || 'N/A', c.pmt?.model || 'N/A', c.pmt?.qe || 'N/A', c.pmt?.hv || 'N/A', c.pmt?.status || 'Empty',
        c.block?.serial || 'N/A', c.block?.status || 'N/A',
        c.fenics?.serial || 'N/A', c.fenics?.status || 'N/A',
        c.divider?.serial || 'N/A', c.divider?.status || 'N/A'
      ]);
    }
    const csvString = rows.map(row => row.join(',')).join('\n');
    const blob = new Blob([csvString], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `tilecal_${modalModule?.barrel}${modalModule?.number}_readout_chain.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  if (isLoading || !detectorData) {
    return (
      <div style={{ height: 'calc(100vh - 100px)' }} className="w-full flex items-center justify-center bg-gray-100 text-blue-900 font-bold font-mono border-round">
        <i className="pi pi-spin pi-spinner mr-3" style={{ fontSize: '2rem' }}></i>
        FETCHING TILECAL MOUNTING STATISTICS...
      </div>
    );
  }

  const renderCellDetails = () => {
    const channels = TILECAL_OPTICS_MAPPING[hoverData.id] || [1, 2]; 
    return (
      <div>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px', marginBottom: '15px' }}>
          <div><b>MODULE</b><br/>{hoverData.barrel}-{hoverData.moduleNumber}</div>
          <div><b>CELL ID</b><br/>{hoverData.id}</div>
        </div>
        {channels.map(ch => {
          const c = pmtData[ch] || {};
          return (
            <div key={ch} style={{ borderTop: '1px dashed #b3e5fc', paddingTop: '10px', marginTop: '10px' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '5px' }}>
                <b>CHANNEL {ch}</b>
                <span style={{ color: getStatusColor(c.pmt?.status), fontWeight: 'bold' }}>{c.pmt ? c.pmt.status : 'EMPTY'}</span>
              </div>
              {c.pmt ? (
                <div style={{ fontSize: '11px', color: '#546e7a' }}>
                  S/N: {c.pmt.serial} | Model: {c.pmt.model}<br/>QE: {c.pmt.qe}% | HV: {c.pmt.hv}V
                </div>
              ) : (
                <div style={{ fontSize: '11px', color: '#9ca3af' }}>No PMT registered in DB</div>
              )}
            </div>
          );
        })}
      </div>
    );
  };

  const renderModuleOverview = () => {
    const stats = hoverData.stats || {};
    const types = Object.keys(stats);
    return (
      <>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '10px' }}>
          <span style={{ fontSize: '18px', fontWeight: 'bold', color: '#000' }}>
            {hoverData.barrelId}-{String(hoverData.sliceIndex + 1).padStart(2, '0')}
          </span>
          <span style={{ color: getStatusColor(hoverData.status), fontWeight: 'bold', fontSize: '16px' }}>
            {hoverData.status}
          </span>
        </div>
        <div style={{ marginTop: '10px', paddingTop: '10px', borderTop: '1px dashed #b3e5fc' }}>
          <b style={{color: '#000', fontSize: '12px'}}>MOUNTING STATISTICS</b>
          {types.length === 0 ? (
            <div style={{ color: '#9ca3af', marginTop: '5px', fontStyle: 'italic' }}>No components registered.</div>
          ) : (
            <div style={{ marginTop: '10px', display: 'flex', flexDirection: 'column', gap: '8px', maxHeight: '200px', overflowY: 'auto', paddingRight: '5px' }}>
              {types.map(type => {
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
              })}
            </div>
          )}
        </div>
      </>
    );
  };

  return (
    <div style={{ height: 'calc(100vh - 100px)', width: '100%', backgroundColor: '#ffffff', borderRadius: '8px', position: 'relative', overflow: 'hidden', boxShadow: '0 4px 10px rgba(0,0,0,0.05)' }}>
      {selectedModule && hoverData && hoverData.clientX && (
        <div style={{ position: 'fixed', left: hoverData.clientX + 15, top: hoverData.clientY + 15, backgroundColor: 'rgba(0, 0, 0, 0.8)', color: '#ffffff', padding: '4px 10px', borderRadius: '4px', fontFamily: 'monospace', fontSize: '14px', fontWeight: 'bold', pointerEvents: 'none', zIndex: 100 }}>
          {hoverData.id}
        </div>
      )}
      
      {/* CONTEXT MENU (RIGHT CLICK) */}
      {contextMenu && (
        <div style={{ position: 'fixed', left: contextMenu.x, top: contextMenu.y, backgroundColor: '#fff', border: '1px solid #ccc', borderRadius: '4px', padding: '5px 0', zIndex: 200, boxShadow: '0 2px 10px rgba(0,0,0,0.2)', width: '180px' }}>
          <div style={{ padding: '8px 15px', color: '#546e7a', fontWeight: 'bold', borderBottom: '1px solid #f0f0f0', fontSize: '12px' }}>
            {contextMenu.barrel}-{contextMenu.number} OPTIONS
          </div>
          <div 
            onClick={openDetailsModal}
            style={{ padding: '10px 15px', cursor: 'pointer', fontSize: '13px', color: '#01579b', transition: 'background 0.2s' }}
            onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#e1f5fe'}
            onMouseLeave={(e) => e.currentTarget.style.backgroundColor = '#fff'}
          >
            <i className="pi pi-external-link mr-2"></i> View Detailed Data
          </div>
        </div>
      )}

      {/* DETAILED DATA FLOATING MATRIX WINDOW (MODAL) */}
      {isModalOpen && (
        <div style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1000, display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
          <div style={{ width: '90%', maxWidth: '1050px', backgroundColor: 'rgba(255,255,255,0.96)', backdropFilter: 'blur(10px)', borderRadius: '8px', padding: '20px', boxShadow: '0 10px 25px rgba(0,0,0,0.3)', position: 'relative' }}>
            
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: '2px solid #0277bd', paddingBottom: '10px', marginBottom: '15px' }}>
              <span style={{ fontSize: '20px', fontWeight: 'bold', color: '#01579b' }}>
                <i className="pi pi-th-large mr-2"></i> {modalModule?.barrel}-{modalModule?.number} Full Electronics Readout Matrix
              </span>
              <div>
                <button onClick={exportToCSV} disabled={!modalPmtData} style={{ padding: '8px 15px', backgroundColor: '#43a047', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer', fontWeight: 'bold', marginRight: '10px', opacity: !modalPmtData ? 0.5 : 1 }}>
                  <i className="pi pi-download mr-2"></i> Export Matrix CSV
                </button>
                <button onClick={() => setIsModalOpen(false)} style={{ padding: '8px 15px', backgroundColor: '#f87171', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer', fontWeight: 'bold' }}>
                  <i className="pi pi-times mr-2"></i> Close
                </button>
              </div>
            </div>

            <div style={{ maxHeight: '65vh', overflowY: 'auto' }}>
              {!modalPmtData ? (
                <div style={{ textAlign: 'center', padding: '40px', color: '#01579b', fontWeight: 'bold', fontSize: '16px' }}>
                  <i className="pi pi-spin pi-spinner mr-3" style={{ fontSize: '1.5rem' }}></i> Rebuilding Chain from Database Structures...
                </div>
              ) : (
                <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '12px', fontFamily: 'monospace' }}>
                  <thead>
                    <tr style={{ backgroundColor: '#e1f5fe', color: '#01579b', textAlign: 'left' }}>
                      <th style={{ padding: '10px', borderBottom: '2px solid #b3e5fc', width: '7%' }}>Channel</th>
                      <th style={{ padding: '10px', borderBottom: '2px solid #b3e5fc', width: '28%' }}>PMT Component</th>
                      <th style={{ padding: '10px', borderBottom: '2px solid #b3e5fc', width: '22%' }}>PMT Block</th>
                      <th style={{ padding: '10px', borderBottom: '2px solid #b3e5fc', width: '22%' }}>FENICS Board</th>
                      <th style={{ padding: '10px', borderBottom: '2px solid #b3e5fc', width: '21%' }}>HV Divider</th>
                    </tr>
                  </thead>
                  <tbody>
                    {Array.from({ length: 48 }).map((_, idx) => {
                      const ch = idx + 1;
                      const c = modalPmtData[ch] || {};
                      return (
                        <tr key={ch} style={{ borderBottom: '1px solid #e2e8f0', transition: 'background 0.15s' }}
                            onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#f8fafc'}
                            onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}>
                          <td style={{ padding: '8px 10px' }}><b>{String(ch).padStart(2, '0')}</b></td>
                          
                          {/* PMT */}
                          <td style={{ padding: '8px 10px' }}>
                            {c.pmt ? (
                              <div onClick={() => { setIsModalOpen(false); navigate(`/equipment/${c.pmt.serial}`); }} style={{ cursor: 'pointer', color: '#0288d1', textDecoration: 'underline' }}>
                                S/N: {c.pmt.serial} ({c.pmt.model}) | <span style={{ color: getStatusColor(c.pmt.status), fontWeight: 'bold' }}>{c.pmt.status}</span>
                              </div>
                            ) : <span style={{ color: '#9ca3af' }}>Empty Channel</span>}
                          </td>

                          {/* PMT Block */}
                          <td style={{ padding: '8px 10px' }}>
                            {c.block ? (
                              <div>S/N: {c.block.serial} | <span style={{ color: getStatusColor(c.block.status) }}>{c.block.status}</span></div>
                            ) : <span style={{ color: '#cbd5e1' }}>--</span>}
                          </td>

                          {/* FENICS Board */}
                          <td style={{ padding: '8px 10px' }}>
                            {c.fenics ? (
                              <div>S/N: {c.fenics.serial} | <span style={{ color: getStatusColor(c.fenics.status) }}>{c.fenics.status}</span></div>
                            ) : <span style={{ color: '#cbd5e1' }}>--</span>}
                          </td>

                          {/* HV Divider */}
                          <td style={{ padding: '8px 10px' }}>
                            {c.divider ? (
                              <div>S/N: {c.divider.serial} | <span style={{ color: getStatusColor(c.divider.status) }}>{c.divider.status}</span></div>
                            ) : <span style={{ color: '#cbd5e1' }}>--</span>}
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              )}
            </div>
          </div>
        </div>
      )}
      
      {/* TOP LEFT CONTROL PANEL */}
      <div style={{ position: 'absolute', top: '15px', left: '15px', color: '#01579b', fontFamily: 'monospace', fontSize: '11px', zIndex: 1, background: 'rgba(255,255,255,0.9)', padding: '15px', border: '1px solid #b3e5fc', borderRadius: '6px', pointerEvents: 'none' }}>
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

      {/* BOTTOM LEFT INSTRUCTIONS & LEGEND */}
      <div style={{ position: 'absolute', bottom: '15px', left: '15px', color: '#546e7a', fontFamily: 'monospace', fontSize: '11px', zIndex: 1, pointerEvents: 'none', background: 'rgba(255,255,255,0.85)', padding: '12px', borderRadius: '6px', border: '1px solid #e0e0e0' }}>
        <b style={{color: '#000'}}>NAVIGATION CONTROLS:</b><br/><br/>
        <div style={{ display: 'flex', alignItems: 'center', marginBottom: '4px' }}><i className="pi pi-window-minimize mr-2" style={{color: '#0288d1'}}></i> Left Click: Enter Module</div>
        <div style={{ display: 'flex', alignItems: 'center', marginBottom: '4px' }}><i className="pi pi-list mr-2" style={{color: '#0288d1'}}></i> Right Click: Module Details</div>
        <div style={{ display: 'flex', alignItems: 'center', marginBottom: '4px' }}><i className="pi pi-sync mr-2" style={{color: '#0288d1'}}></i> Left Drag: Rotate Camera</div>
        <div style={{ display: 'flex', alignItems: 'center', marginBottom: '4px' }}><i className="pi pi-search mr-2" style={{color: '#0288d1'}}></i> Scroll: Zoom In/Out</div>
        <div style={{ display: 'flex', alignItems: 'center', marginBottom: '10px' }}><i className="pi pi-arrows-alt mr-2" style={{color: '#0288d1'}}></i> Right Drag: Pan View</div>
        
        {(selectedModule || diagnosticMode || hoverData) && (
          <div style={{ borderTop: '1px dashed #b3e5fc', paddingTop: '10px' }}>
            <b style={{color: '#000'}}>STATUS LEGEND:</b><br/>
            <span style={{ color: '#4ade80' }}>██</span> Nominal (All OK)<br/>
            <span style={{ color: '#fbbf24' }}>██</span> Warning (Unknown/Testing)<br/>
            <span style={{ color: '#f87171' }}>██</span> Alert (Failed/Broken)<br/>
            <span style={{ color: '#9ca3af' }}>██</span> Empty
          </div>
        )}
      </div>

      <Canvas camera={{ position: [22, 12, 22], fov: 20 }} style={{ width: '100%', height: '100%', display: 'block' }}>
        <CameraManager selectedModule={selectedModule} controlsRef={controlsRef} />
        <ambientLight intensity={1.5} />
        <directionalLight position={[10, 15, 10]} intensity={2} color="#ffffff" />
        <directionalLight position={[-10, -10, -10]} intensity={0.5} color="#90caf9" />

        {!selectedModule && <Grid position={[0, -3.5, 0]} args={[40, 40]} sectionColor="#e0e0e0" cellColor="#f5f5f5" infiniteGrid />}
        {selectedModule ? (
          <group scale={0.08} position={[0, -1.5, 0]}>
            <ModuleCells3D barrel={selectedModule.barrel} moduleNumber={selectedModule.number} onHoverCell={setHoverData} />
          </group>
        ) : (
          <group>
            {BARRELS.map(b => <BarrelAssembly key={b.id} id={b.id} zOffset={b.zOffset} setHoverData={setHoverData} diagnosticMode={diagnosticMode} mockData={detectorData} onModuleClick={handleModuleClick} onContextMenu={handleRightClick} />)}
          </group>
        )}
        <OrbitControls ref={controlsRef} enablePan={true} enableZoom={true} enableRotate={!selectedModule} />
      </Canvas>

      {/* BOTTOM RIGHT DYNAMIC HOVER PANEL */}
      {hoverData && !isModalOpen && !contextMenu && (
        <div style={{ position: 'absolute', bottom: '20px', right: '20px', backgroundColor: 'rgba(255, 255, 255, 0.95)', color: '#01579b', padding: '20px', borderRadius: '8px', fontFamily: 'monospace', fontSize: '13px', border: '1px solid #0277bd', width: '320px', pointerEvents: 'none', boxShadow: '0 4px 15px rgba(0,0,0,0.1)' }}>
          <div style={{ borderBottom: '1px solid #0277bd', paddingBottom: '10px', marginBottom: '15px', fontSize: '16px', fontWeight: 'bold' }}>
            {selectedModule ? 'CELL DIAGNOSTIC' : 'MODULE OVERVIEW'}
          </div>
          {selectedModule ? renderCellDetails() : renderModuleOverview()}
        </div>
      )}
    </div>
  );
}