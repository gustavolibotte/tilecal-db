import { useState, useRef, useEffect } from 'react';
import { Canvas, useFrame, useThree } from '@react-three/fiber';
import { OrbitControls, Grid, Edges, Billboard, Text } from '@react-three/drei';
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
  if (['INSTALLED', 'TESTED', 'DELIVERED', 'NOMINAL'].includes(s)) return '#4ade80'; 
  if (['TESTING', 'DEVELOPMENT', 'PREPRODUCTION'].includes(s)) return '#fbbf24'; 
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

function IndustrialSlice({ barrelId, sliceIndex, thetaStart, thetaLength, zOffset, setHoverData, diagnosticMode, moduleData, onModuleClick }: any) {
  const meshRef = useRef<THREE.Mesh>(null!);
  const [hovered, setHover] = useState(false);
  const technicalColor = sliceIndex % 2 === 0 ? '#b3e5fc' : '#e1f5fe';
  let color = hovered ? '#ffbb00' : technicalColor; 

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
    >
      <cylinderGeometry args={[2.7, 2.7, 2, 1, 1, false, 0, thetaLength]} />
      <meshStandardMaterial color={color} side={THREE.DoubleSide} roughness={0.1} metalness={0.2} transparent={true} opacity={hovered ? 0.9 : (diagnosticMode ? 0.8 : 0.65)} />
      <Edges linewidth={1} threshold={15} color="#546e7a" />
    </mesh>
  );
}

function BarrelAssembly({ id, zOffset, setHoverData, diagnosticMode, mockData, onModuleClick }: any) {
  const slices = 64;
  const thetaLength = (Math.PI * 2) / slices;
  return (
    <group rotation={[Math.PI / 2, 0, 0]}>
      {Array.from({ length: slices }).map((_, i) => (
        <group key={i}>
          <IndustrialSlice barrelId={id} sliceIndex={i} thetaStart={i * thetaLength} thetaLength={thetaLength} zOffset={zOffset} setHoverData={setHoverData} diagnosticMode={diagnosticMode} moduleData={mockData?.[id]?.[i]} onModuleClick={onModuleClick} />
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
  const [hoverData, setHoverData] = useState<any>(null);
  const [diagnosticMode, setDiagnosticMode] = useState(false);
  const [selectedModule, setSelectedModule] = useState<{ barrel: string; number: string } | null>(null);
  
  const [detectorData, setDetectorData] = useState<Record<string, any[]> | null>(null);
  const [pmtData, setPmtData] = useState<Record<string, any>>({}); 
  const [isLoading, setIsLoading] = useState(true);
  const controlsRef = useRef<any>(null);

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

  const handleModuleClick = (barrel: string, moduleNumber: string) => { setSelectedModule({ barrel, number: moduleNumber }); setHoverData(null); };
  const handleBackToGlobal = () => { setSelectedModule(null); setHoverData(null); };

  if (isLoading || !detectorData) {
    return (
      <div style={{ height: 'calc(100vh - 100px)' }} className="w-full flex items-center justify-center bg-gray-100 text-blue-900 font-bold font-mono border-round">
        <i className="pi pi-spin pi-spinner mr-3" style={{ fontSize: '2rem' }}></i>
        SYNCING WITH ELECTRONICS DB...
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
          const pmt = pmtData[ch];
          return (
            <div key={ch} style={{ borderTop: '1px dashed #b3e5fc', paddingTop: '10px', marginTop: '10px' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '5px' }}>
                <b>CHANNEL {ch}</b>
                <span style={{ color: getStatusColor(pmt?.status), fontWeight: 'bold' }}>{pmt ? pmt.status : 'EMPTY'}</span>
              </div>
              {pmt ? (
                <div style={{ fontSize: '11px', color: '#546e7a' }}>
                  S/N: {pmt.serial} | Model: {pmt.model}<br/>QE: {pmt.qe}% | HV: {pmt.hv}V
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

  return (
    // Aqui está a MÁGICA: `height: calc(100vh - 100px)` obriga o 3D a usar a tela toda independentemente do pai.
    <div style={{ height: 'calc(100vh - 100px)', width: '100%', backgroundColor: '#ffffff', borderRadius: '8px', position: 'relative', overflow: 'hidden', boxShadow: '0 4px 10px rgba(0,0,0,0.05)' }}>
      {selectedModule && hoverData && hoverData.clientX && (
        <div style={{ position: 'fixed', left: hoverData.clientX + 15, top: hoverData.clientY + 15, backgroundColor: 'rgba(0, 0, 0, 0.8)', color: '#ffffff', padding: '4px 10px', borderRadius: '4px', fontFamily: 'monospace', fontSize: '14px', fontWeight: 'bold', pointerEvents: 'none', zIndex: 100 }}>
          {hoverData.id}
        </div>
      )}
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
            STATUS: <span style={{ color: '#43a047' }}>LIVE DATA</span><br/>DATA SYNC: API CONNECTED<br/>MODULES: 256 / 256<br/><br/>
            <div style={{ display: 'flex', alignItems: 'center', cursor: 'pointer', borderTop: '1px dashed #b3e5fc', paddingTop: '10px' }} onClick={() => setDiagnosticMode(!diagnosticMode)}>
              <div style={{ width: '36px', height: '18px', backgroundColor: diagnosticMode ? '#43a047' : '#cfd8dc', borderRadius: '18px', position: 'relative', marginRight: '10px' }}>
                <div style={{ width: '14px', height: '14px', backgroundColor: '#fff', borderRadius: '50%', position: 'absolute', top: '2px', left: diagnosticMode ? '20px' : '2px' }}/>
              </div>
              <b style={{ color: diagnosticMode ? '#000' : '#78909c' }}>GLOBAL DIAGNOSTIC</b>
            </div>
          </div>
        )}
      </div>
      <div style={{ position: 'absolute', bottom: '15px', left: '15px', color: '#546e7a', fontFamily: 'monospace', fontSize: '10px', zIndex: 1, pointerEvents: 'none' }}>
        LEGEND:<br/>
        {selectedModule ? (<><span style={{ color: '#ffffff' }}>██</span> Hovered / <span style={{ color: '#37c871' }}>██</span> Nominal / <span style={{ color: '#e02f1f' }}>██</span> Alert</>) 
        : diagnosticMode ? (<><span style={{ color: '#ffbb00' }}>██</span> Hovered / <span style={{ color: '#4ade80' }}>██</span> Nominal / <span style={{ color: '#f87171' }}>██</span> Alert / <span style={{ color: '#9ca3af' }}>██</span> Empty</>) 
        : (<><span style={{ color: '#ffbb00' }}>██</span> Hovered / <span style={{ color: '#b3e5fc' }}>██</span> Module A / <span style={{ color: '#e1f5fe' }}>██</span> Module B</>)}
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
            {BARRELS.map(b => <BarrelAssembly key={b.id} id={b.id} zOffset={b.zOffset} setHoverData={setHoverData} diagnosticMode={diagnosticMode} mockData={detectorData} onModuleClick={handleModuleClick} />)}
          </group>
        )}
        <OrbitControls ref={controlsRef} enablePan={true} enableZoom={true} enableRotate={!selectedModule} />
      </Canvas>

      {hoverData && (
        <div style={{ position: 'absolute', bottom: '20px', right: '20px', backgroundColor: 'rgba(255, 255, 255, 0.95)', color: '#01579b', padding: '20px', borderRadius: '8px', fontFamily: 'monospace', fontSize: '13px', border: '1px solid #0277bd', width: '320px', pointerEvents: 'none', boxShadow: '0 4px 15px rgba(0,0,0,0.1)' }}>
          <div style={{ borderBottom: '1px solid #0277bd', paddingBottom: '10px', marginBottom: '15px', fontSize: '16px', fontWeight: 'bold' }}>
            {selectedModule ? 'CELL DIAGNOSTIC' : 'MODULE OVERVIEW'}
          </div>
          {selectedModule ? renderCellDetails() : (
            <>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px' }}>
                <div><b>SECTION</b><br/>{hoverData.barrelId}</div>
                <div><b>SLOT</b><br/>#{String(hoverData.sliceIndex + 1).padStart(2, '0')}</div>
                <div><b>TEMP (Core)</b><br/>{hoverData.temperature?.toFixed(1) || '--'} °C</div>
                <div><b>PWR DRAW</b><br/>{hoverData.powerDraw?.toFixed(1) || '--'} W</div>
              </div>
              <div style={{ marginTop: '15px', paddingTop: '10px', borderTop: '1px dashed #b3e5fc', display: 'flex', justifyContent: 'space-between' }}>
                <b>S/N: {hoverData.serial}</b>
                <span style={{ color: getStatusColor(hoverData.status), fontWeight: 'bold' }}>{hoverData.status}</span>
              </div>
            </>
          )}
        </div>
      )}
    </div>
  );
}