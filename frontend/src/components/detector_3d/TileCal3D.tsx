import { useState, useRef, useMemo } from 'react';
import { Canvas, useFrame } from '@react-three/fiber';
import { OrbitControls, Grid, Edges, Billboard, Text } from '@react-three/drei';
import * as THREE from 'three';
import { ModuleCells3D } from './ModuleCells3D';

const BARRELS = [
  { id: 'EBA', zOffset: -3.35, label: 'EXTENDED BARREL A' },
  { id: 'LBA', zOffset: -1.05, label: 'LONG BARREL A' },
  { id: 'LBC', zOffset:  1.05, label: 'LONG BARREL C' },
  { id: 'EBC', zOffset:  3.35, label: 'EXTENDED BARREL C' },
];

function generateMockData() {
  const data: Record<string, any[]> = { EBA: [], LBA: [], LBC: [], EBC: [] };
  BARRELS.forEach(b => {
    for (let i = 0; i < 64; i++) {
      const isError = Math.random() > 0.95;
      data[b.id].push({
        temperature: isError ? 35 + Math.random() * 10 : 20 + Math.random() * 5,
        status: isError ? 'ALERT' : 'NOMINAL',
        powerDraw: isError ? 0.0 : 4.0 + Math.random(),
        serial: `TC-${b.id}-M${String(i + 1).padStart(2, '0')}`
      });
    }
  });
  return data;
}

function IndustrialSlice({ barrelId, sliceIndex, thetaStart, thetaLength, zOffset, setHoverData, diagnosticMode, moduleData, onModuleClick }: any) {
  const meshRef = useRef<THREE.Mesh>(null!);
  const [hovered, setHover] = useState(false);
  
  const technicalColor = sliceIndex % 2 === 0 ? '#b3e5fc' : '#e1f5fe';
  let color = hovered ? '#ffbb00' : technicalColor; 

  if (diagnosticMode && !hovered) {
    color = moduleData.status === 'NOMINAL' ? '#4ade80' : '#f87171'; // Verde ou Vermelho
  }

  useFrame(() => {
    if (!meshRef.current) return;
    const scale = THREE.MathUtils.lerp(meshRef.current.scale.x, hovered ? 1.04 : 1, 0.2);
    meshRef.current.scale.set(scale, scale, 1);
  });

  const handlePointerOver = (e: any) => {
    e.stopPropagation();
    setHover(true);
    setHoverData({
      barrelId,
      sliceIndex,
      ...moduleData
    });
  };

  const handlePointerOut = (e: any) => {
    e.stopPropagation();
    setHover(false);
    setHoverData(null);
  };

  const handleClick = (e: any) => {
    e.stopPropagation();
    onModuleClick(barrelId, String(sliceIndex + 1).padStart(2, '0'));
  };

  return (
    <mesh
      ref={meshRef}
      position={[0, zOffset, 0]}
      rotation={[0, thetaStart, 0]}
      onPointerOver={handlePointerOver}
      onPointerOut={handlePointerOut}
      onClick={handleClick}
    >
      <cylinderGeometry args={[2.7, 2.7, 2, 1, 1, false, 0, thetaLength]} />
      <meshStandardMaterial 
        color={color} 
        side={THREE.DoubleSide}
        roughness={0.1}
        metalness={0.2} 
        transparent={true} 
        opacity={hovered ? 0.9 : (diagnosticMode ? 0.8 : 0.65)}
      />
      <Edges linewidth={1} threshold={15} color="#546e7a" />
    </mesh>
  );
}

function BarrelAssembly({ id, zOffset, setHoverData, diagnosticMode, mockData, onModuleClick }: any) {
  const slices = 64;
  const thetaLength = (Math.PI * 2) / slices;

  return (
    <group rotation={[Math.PI / 2, 0, 0]}>
      {Array.from({ length: slices }).map((_, i) => {
        const isLabel = i % 8 === 0;
        const ang = i * thetaLength + thetaLength / 2;
        const radius = 3.2; 
        const x = Math.sin(ang) * radius;
        const z = Math.cos(ang) * radius;

        return (
          <group key={i}>
            <IndustrialSlice
              barrelId={id}
              sliceIndex={i}
              thetaStart={i * thetaLength}
              thetaLength={thetaLength}
              zOffset={zOffset}
              setHoverData={setHoverData}
              diagnosticMode={diagnosticMode}
              moduleData={mockData[id][i]}
              onModuleClick={onModuleClick}
            />
            
            {isLabel && (
              <Billboard position={[x, zOffset, z]}>
                <Text fontSize={0.18} color="#0277bd" fontWeight="bold">
                  {String(i + 1).padStart(2, '0')}
                </Text>
              </Billboard>
            )}
          </group>
        );
      })}
      
      <Billboard position={[4.0, zOffset, 2.0]}>
        <Text fontSize={0.35} color="#1e88e5" fontWeight="bold" anchorX="center" anchorY="bottom">
          {id}
        </Text>
      </Billboard>
    </group>
  );
}

export default function TileCal3D() {
  const [hoverData, setHoverData] = useState<any>(null);
  const [diagnosticMode, setDiagnosticMode] = useState(false);
  const [selectedModule, setSelectedModule] = useState<{ barrel: string; number: string } | null>(null);
  
  const mockData = useMemo(() => generateMockData(), []);

  const handleModuleClick = (barrel: string, moduleNumber: string) => {
    setSelectedModule({ barrel, number: moduleNumber });
    setHoverData(null);
  };

  const handleBackToGlobal = () => {
    setSelectedModule(null);
    setHoverData(null);
  };

  return (
    <div style={{ height: '100%', width: '100%', minHeight: '500px', backgroundColor: '#ffffff', borderRadius: '8px', position: 'relative', overflow: 'hidden' }}>
      
      <div style={{ position: 'absolute', top: '15px', left: '15px', color: '#01579b', fontFamily: 'monospace', fontSize: '11px', zIndex: 1, background: 'rgba(255,255,255,0.9)', padding: '15px', border: '1px solid #b3e5fc', borderRadius: '6px', boxShadow: '0 2px 10px rgba(0,0,0,0.05)', pointerEvents: 'none' }}>
        <b style={{ fontSize: '13px', color: '#000' }}>ATLAS TILECAL SYS</b><br/><br/>
        
        {selectedModule ? (
          <div style={{ pointerEvents: 'auto' }}>
            VIEW: <span style={{ color: '#0277bd', fontWeight: 'bold' }}>CELL DIAGNOSTIC</span><br/>
            TARGET: {selectedModule.barrel}-{selectedModule.number}<br/><br/>
            <button 
              onClick={handleBackToGlobal}
              style={{
                width: '100%', padding: '8px', backgroundColor: '#0288d1', color: 'white', border: 'none', 
                borderRadius: '4px', cursor: 'pointer', fontWeight: 'bold', display: 'flex', alignItems: 'center', justifyContent: 'center'
              }}
            >
              <i className="bi bi-arrow-left" style={{ marginRight: '6px' }}></i> BACK TO GLOBAL
            </button>
          </div>
        ) : (
          <div style={{ pointerEvents: 'auto' }}>
            STATUS: <span style={{ color: '#43a047' }}>NOMINAL</span><br/>
            DATA SYNC: 40 MHz<br/>
            MODULES: 256 / 256<br/><br/>
            
            <div style={{ display: 'flex', alignItems: 'center', cursor: 'pointer', borderTop: '1px dashed #b3e5fc', paddingTop: '10px' }} onClick={() => setDiagnosticMode(!diagnosticMode)}>
              <div style={{ 
                width: '36px', height: '18px', backgroundColor: diagnosticMode ? '#43a047' : '#cfd8dc', 
                borderRadius: '18px', position: 'relative', marginRight: '10px', transition: 'background-color 0.3s'
              }}>
                <div style={{
                  width: '14px', height: '14px', backgroundColor: '#fff', borderRadius: '50%',
                  position: 'absolute', top: '2px', left: diagnosticMode ? '20px' : '2px', transition: 'left 0.3s',
                  boxShadow: '0 1px 3px rgba(0,0,0,0.3)'
                }}/>
              </div>
              <b style={{ color: diagnosticMode ? '#000' : '#78909c' }}>GLOBAL DIAGNOSTIC</b>
            </div>
          </div>
        )}
      </div>

      <div style={{ position: 'absolute', bottom: '15px', left: '15px', color: '#546e7a', fontFamily: 'monospace', fontSize: '10px', zIndex: 1, pointerEvents: 'none' }}>
        LEGEND:<br/>
        {selectedModule ? (
           <><span style={{ color: '#ffffff' }}>██</span> Hovered / <span style={{ color: '#37c871' }}>██</span> Nominal / <span style={{ color: '#e02f1f' }}>██</span> Alert</>
        ) : diagnosticMode ? (
           <><span style={{ color: '#ffbb00' }}>██</span> Hovered / <span style={{ color: '#4ade80' }}>██</span> Nominal / <span style={{ color: '#f87171' }}>██</span> Alert</>
        ) : (
           <><span style={{ color: '#ffbb00' }}>██</span> Hovered / <span style={{ color: '#b3e5fc' }}>██</span> Módulo A / <span style={{ color: '#e1f5fe' }}>██</span> Módulo B</>
        )}
      </div>

      <Canvas camera={{ position: [22, 12, 22], fov: 20 }}>
        <ambientLight intensity={1.5} />
        <directionalLight position={[10, 15, 10]} intensity={2} color="#ffffff" />
        <directionalLight position={[-10, -10, -10]} intensity={0.5} color="#90caf9" />

        <Grid position={[0, -3.5, 0]} args={[40, 40]} sectionColor="#e0e0e0" cellColor="#f5f5f5" infiniteGrid />

        {selectedModule ? (
          <group scale={0.08} position={[0, -1.5, 0]}>
            <ModuleCells3D 
              barrel={selectedModule.barrel} 
              moduleNumber={selectedModule.number} 
              onHoverCell={setHoverData}
            />
          </group>
        ) : (
          <group>
            {BARRELS.map(b => (
              <BarrelAssembly 
                key={b.id} 
                id={b.id} 
                label={b.label} 
                zOffset={b.zOffset} 
                setHoverData={setHoverData}
                diagnosticMode={diagnosticMode}
                mockData={mockData}
                onModuleClick={handleModuleClick}
              />
            ))}
          </group>
        )}

        <OrbitControls enablePan={true} enableZoom={true} enableRotate={true} />
      </Canvas>

      {hoverData && (
        <div style={{
          position: 'absolute',
          bottom: '20px',
          right: '20px',
          backgroundColor: 'rgba(255, 255, 255, 0.95)',
          color: '#01579b',
          padding: '20px',
          borderRadius: '8px',
          fontFamily: 'monospace',
          fontSize: '13px',
          border: '1px solid #0277bd',
          width: '320px',
          pointerEvents: 'none',
          boxShadow: '0 4px 15px rgba(0,0,0,0.1)',
          backdropFilter: 'blur(4px)',
          zIndex: 10
        }}>
          <div style={{ borderBottom: '1px solid #0277bd', paddingBottom: '10px', marginBottom: '15px', fontSize: '16px', fontWeight: 'bold', display: 'flex', justifyContent: 'space-between' }}>
            <span>{selectedModule ? 'CELL DIAGNOSTIC' : 'MODULE DIAGNOSTIC'}</span>
            <span style={{ color: hoverData.status === 'NOMINAL' ? '#43a047' : '#e53935', fontWeight: 'bold' }}>{hoverData.status}</span>
          </div>
          
          {selectedModule ? (
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px' }}>
              <div><b>MODULE</b><br/>{hoverData.barrel}-{hoverData.moduleNumber}</div>
              <div><b>CELL ID</b><br/>{hoverData.id}</div>
              <div><b>TEMP</b><br/>{hoverData.temp}</div>
              <div><b>PMT HV</b><br/>{hoverData.pmtHV}</div>
            </div>
          ) : (
            <>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px' }}>
                <div><b>SECTION</b><br/>{hoverData.barrelId}</div>
                <div><b>SLOT</b><br/>#{String(hoverData.sliceIndex + 1).padStart(2, '0')}</div>
                <div><b>TEMP (Core)</b><br/>{hoverData.temperature.toFixed(1)} °C</div>
                <div><b>PWR DRAW</b><br/>{hoverData.powerDraw.toFixed(1)} W</div>
              </div>
              <div style={{ marginTop: '15px', paddingTop: '10px', borderTop: '1px dashed #b3e5fc', color: '#546e7a' }}>
                <b>S/N:</b> <span style={{color: '#000'}}>{hoverData.serial}</span>
              </div>
            </>
          )}
        </div>
      )}
    </div>
  );
}