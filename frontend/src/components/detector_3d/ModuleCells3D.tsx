import React, { useMemo, useState } from 'react';
import { Center } from '@react-three/drei';
import * as THREE from 'three';
import { LBA_CELLS, EBA_CELLS, EXTRUDE_SETTINGS } from './cellGeometry';

// Helper de conversão de cores consistente
const getStatusColor = (statusName: string) => {
  const s = statusName?.toUpperCase() || '';
  if (['INSTALLED', 'TESTED', 'DELIVERED', 'NOMINAL', 'GOOD', 'OK', 'WORKING'].includes(s)) return '#4ade80'; 
  if (['TESTING', 'DEVELOPMENT', 'PREPRODUCTION', 'PRODUCED', 'REPAIRED', 'UNKNOWN', 'SPARE', 'WARNING'].includes(s)) return '#fbbf24'; 
  if (['FAILURE', 'BROKEN', 'LOST', 'ALERT'].includes(s)) return '#f87171'; 
  return '#9ca3af'; 
};

interface ModuleCells3DProps {
  barrel: string; // 'LBA', 'LBC', 'EBA', 'EBC'
  moduleNumber: string; // '01' to '64'
  onHoverCell: (cellData: any) => void;
  onClickCell?: (cellId: string) => void;
  diagnosticMode?: boolean;
  cellStatuses?: Record<string, string>;
}

export const ModuleCells3D: React.FC<ModuleCells3DProps> = ({ barrel, moduleNumber, onHoverCell, onClickCell, diagnosticMode, cellStatuses = {} }) => {
  const [hoveredCell, setHoveredCell] = useState<string | null>(null);

  const isLongBarrel = barrel === 'LBA' || barrel === 'LBC';
  const shapes = isLongBarrel ? LBA_CELLS : EBA_CELLS;
  
  // Mirroring logic: SVG Y-axis grows downwards, ThreeJS grows upwards (-1)
  // LBC and EBC must also be mirrored on the X-axis (-1)
  const isC_Side = barrel === 'LBC' || barrel === 'EBC';
  const scale: [number, number, number] = [isC_Side ? -1 : 1, -1, 1];

  return (
    <Center>
      <group scale={scale}>
        {Object.entries(shapes).map(([cellId, shape]) => {
          const status = cellStatuses[cellId];
          
          // Lógica Consistente de Cores
          let currentColor = '#e1f5fe'; // Cor neutra base (igual ao calorímetro global)
          if (hoveredCell === cellId) {
            currentColor = '#ffbb00'; // Cor de Hover (amarelo igual ao calorímetro)
          } else if (diagnosticMode) {
            currentColor = getStatusColor(status); // Cor de Status se o modo Global Diagnostic estiver ativo
          }

          return (
            <mesh
              key={cellId}
              onPointerOver={(e) => {
                e.stopPropagation();
                setHoveredCell(cellId);
                onHoverCell({ barrel, moduleNumber, id: cellId, status, clientX: e.clientX, clientY: e.clientY });
              }}
              onPointerMove={(e) => {
                e.stopPropagation();
                onHoverCell({ barrel, moduleNumber, id: cellId, status, clientX: e.clientX, clientY: e.clientY });
              }}
              onPointerOut={(e) => {
                e.stopPropagation();
                setHoveredCell(null);
                onHoverCell(null);
              }}
              onClick={(e) => {
                e.stopPropagation();
                if (onClickCell) onClickCell(cellId);
              }}
            >
              <extrudeGeometry args={[shape as THREE.Shape, EXTRUDE_SETTINGS]} />
              <meshStandardMaterial 
                color={currentColor} 
                roughness={0.3}
                metalness={0.7}
                opacity={0.9}
                transparent
              />
              <lineSegments>
                <edgesGeometry args={[new THREE.ExtrudeGeometry(shape as THREE.Shape, EXTRUDE_SETTINGS)]} />
                <lineBasicMaterial color="#141415" linewidth={1} />
              </lineSegments>
            </mesh>
          );
        })}
      </group>
    </Center>
  );
};