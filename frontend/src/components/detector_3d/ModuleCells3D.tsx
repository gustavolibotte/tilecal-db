import { useState, useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import { Edges } from '@react-three/drei';
import * as THREE from 'three';

// Helper de conversão de cores consistente com o restante da UI
const getStatusColor = (statusName: string) => {
  const s = statusName?.toUpperCase() || '';
  if (['INSTALLED', 'TESTED', 'DELIVERED', 'NOMINAL', 'GOOD', 'OK', 'WORKING'].includes(s)) return '#4ade80'; 
  if (['TESTING', 'DEVELOPMENT', 'PREPRODUCTION', 'PRODUCED', 'REPAIRED', 'UNKNOWN', 'SPARE', 'WARNING'].includes(s)) return '#fbbf24'; 
  if (['FAILURE', 'BROKEN', 'LOST', 'ALERT'].includes(s)) return '#f87171'; 
  return '#9ca3af'; 
};

export function ModuleCells3D({ barrel, moduleNumber, onHoverCell, onClickCell, diagnosticMode, cellStatuses }: any) {
  // Configuração neutra idêntica à casca do calorímetro
  const NEUTRAL_COLOR = '#e1f5fe';
  const HOVER_COLOR = '#ffbb00';

  // Células baseadas nas chaves dinâmicas
  const cells = Object.keys(cellStatuses || {});

  return (
    <group>
      {cells.map((cellId, index) => {
        // Distribuição simples em Z e X para visualização das fatias (adapte à sua topologia 3D)
        const zPos = (index % 10) * 4 - 18;
        const xPos = Math.floor(index / 10) * 4 - 6;
        
        return (
          <CellMesh 
            key={cellId} 
            cellId={cellId} 
            barrel={barrel}
            moduleNumber={moduleNumber}
            position={[xPos, 0, zPos]} 
            status={cellStatuses[cellId]}
            diagnosticMode={diagnosticMode}
            onHover={onHoverCell}
            onClick={onClickCell}
            neutralColor={NEUTRAL_COLOR}
            hoverColor={HOVER_COLOR}
          />
        );
      })}
    </group>
  );
}

function CellMesh({ cellId, barrel, moduleNumber, position, status, diagnosticMode, onHover, onClick, neutralColor, hoverColor }: any) {
  const meshRef = useRef<THREE.Mesh>(null!);
  const [hovered, setHover] = useState(false);

  // Lógica Consistente de Cores: 
  let currentColor = neutralColor;
  if (hovered) currentColor = hoverColor;
  else if (diagnosticMode) currentColor = getStatusColor(status);

  useFrame(() => {
    if (!meshRef.current) return;
    const targetScale = hovered ? 1.05 : 1;
    meshRef.current.scale.lerp(new THREE.Vector3(targetScale, targetScale, targetScale), 0.2);
  });

  return (
    <mesh
      ref={meshRef}
      position={position}
      onPointerOver={(e: any) => {
        e.stopPropagation();
        setHover(true);
        onHover({ id: cellId, barrel, moduleNumber, clientX: e.clientX, clientY: e.clientY });
      }}
      onPointerMove={(e: any) => {
        e.stopPropagation();
        // Permite que o tooltip acompanhe o rato de forma fluida
        onHover({ id: cellId, barrel, moduleNumber, clientX: e.clientX, clientY: e.clientY });
      }}
      onPointerOut={(e: any) => {
        e.stopPropagation();
        setHover(false);
        onHover(null);
      }}
      onClick={(e: any) => {
        e.stopPropagation();
        onClick(cellId);
      }}
    >
      <boxGeometry args={[3.5, 3.5, 3.5]} />
      <meshStandardMaterial 
        color={currentColor} 
        roughness={0.2} 
        metalness={0.1}
        transparent={true} 
        opacity={diagnosticMode ? 0.9 : 0.7} 
      />
      <Edges linewidth={1} threshold={15} color="#546e7a" />
    </mesh>
  );
}