/**
 * Career Progression Visualization
 * Enhanced D3.js implementation with zoom, pan, and multiple view types
 */

let careerData = null;
let selectedNode = null;
let currentChartId = null;
let currentView = 'tree';
let zoomBehavior = null;
let svgGroup = null;

function initCareerVisualization(chartId, options) {
    const container = document.getElementById(chartId);
    if (!container) return;
    
    currentChartId = chartId;
    currentView = 'tree'; // Always use tree view
    
    // Load career data via AJAX
    jQuery.ajax({
        url: cpv_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'cpv_get_career_data',
            nonce: cpv_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                careerData = response.data;
                container.querySelector('.cpv-loading').style.display = 'none';
                
                // Always create tree visualization
                createHierarchicalVisualization(chartId, careerData);
                
                // Setup controls
                setupControls(chartId);
            }
        },
        error: function() {
            container.innerHTML = '<p class="error">Failed to load career data</p>';
        }
    });
}

function updateVisualization(viewType) {
    currentView = viewType;
    
    switch(viewType) {
        case 'timeline':
            createTimelineVisualization(currentChartId, careerData);
            break;
        case 'graph':
            createGraphVisualization(currentChartId, careerData);
            break;
        case 'sankey':
            createSankeyVisualization(currentChartId, careerData);
            break;
        case 'tree':
        default:
            createHierarchicalVisualization(currentChartId, careerData);
            break;
    }
}

function createHierarchicalVisualization(chartId, data) {
    const container = document.getElementById(chartId);
    const containerParent = container.parentElement;
    
    // Clear previous visualization
    d3.select(container).selectAll('svg').remove();
    
    // Get container dimensions
    const containerWidth = container.offsetWidth || 800;
    const containerHeight = parseInt(containerParent.querySelector('.cpv-chart').style.height) || 600;
    
    // Create square layout with better spacing
    const size = Math.min(containerWidth, containerHeight);
    const margin = {
        top: 60,
        right: 60,
        bottom: 60,
        left: 60
    };
    
    const width = size - margin.left - margin.right;
    const height = size - margin.top - margin.bottom;
    
    // Create SVG with viewBox for responsive scaling
    const svg = d3.select(container)
        .append('svg')
        .attr('width', '100%')
        .attr('height', '100%')
        .attr('viewBox', `0 0 ${size} ${size}`)
        .attr('preserveAspectRatio', 'xMidYMid meet');
    
    // Add zoom behavior
    zoomBehavior = d3.zoom()
        .scaleExtent([0.5, 3])
        .on('zoom', (event) => {
            svgGroup.attr('transform', event.transform);
        });
    
    svg.call(zoomBehavior);
    
    // Create main group for zoom/pan
    svgGroup = svg.append('g')
        .attr('transform', `translate(${margin.left},${margin.top})`);
    
    // Create hierarchical layout with better spacing
    const root = d3.hierarchy(data);
    const treeLayout = d3.tree()
        .size([width, height])
        .separation((a, b) => {
            // Extra separation for path nodes to prevent label overlap
            if (a.data.type === 'path' || b.data.type === 'path') {
                return 8;  // Much more space between paths
            }
            // Much more separation to prevent text overlap
            if (a.parent === b.parent) {
                return 4.5;  // Increased from 2.5
            }
            return 6;  // Increased from 3.5
        });
    
    treeLayout(root);
    
    // Find the actual career span from the data
    const allYears = [];
    root.descendants().forEach(node => {
        if (node.data.startYear) allYears.push(node.data.startYear);
        if (node.data.endYear) allYears.push(node.data.endYear);
    });
    
    const minYear = Math.min(...allYears.filter(y => y > 1990)) || 2000;
    const maxYear = Math.max(...allYears.filter(y => y < 2100)) || new Date().getFullYear() + 1;
    
    // Round to nearest 5 years for cleaner axis
    const domainMin = Math.floor(minYear / 5) * 5;
    const domainMax = Math.ceil(maxYear / 5) * 5;
    
    // Create time scale for horizontal positioning (flipped axis)
    const timeScale = d3.scaleLinear()
        .domain([domainMin, domainMax])
        .range([20, width - 20]);
    
    // Group jobs by path for collision detection
    const jobsByPath = {};
    root.descendants().forEach(node => {
        if (node.data.type === 'job') {
            const pathParent = node.parent;
            if (pathParent && pathParent.data.type === 'path') {
                if (!jobsByPath[pathParent.data.name]) {
                    jobsByPath[pathParent.data.name] = [];
                }
                jobsByPath[pathParent.data.name].push(node);
            }
        }
    });
    
    // Adjust node positions based on time with collision detection
    root.descendants().forEach(node => {
        // Store the original x position as the vertical position
        const verticalPos = node.x;
        const horizontalPos = node.y;
        
        if (node.data.type === 'path' && node.children && node.children.length > 0) {
            const earliestYear = Math.min(...node.children.map(child => child.data.startYear || domainMax));
            // Move path nodes to the left to make room for labels
            node.x = timeScale(earliestYear) - 80;
            node.y = verticalPos;
        } else {
            // Keep vertical position from tree layout
            node.y = verticalPos;
        }
    });
    
    // Position jobs with collision detection
    Object.keys(jobsByPath).forEach(pathName => {
        const jobs = jobsByPath[pathName];
        
        // Sort jobs by year
        jobs.sort((a, b) => (a.data.startYear || 0) - (b.data.startYear || 0));
        
        // Apply minimum spacing between jobs
        const minSpacing = 60; // Minimum horizontal pixels between job nodes
        let lastX = -Infinity;
        
        jobs.forEach(job => {
            const targetX = job.data.startYear ? timeScale(job.data.startYear) : 100;
            
            // If too close to previous job, push it right
            if (targetX < lastX + minSpacing) {
                job.x = lastX + minSpacing;
            } else {
                job.x = targetX;
            }
            
            lastX = job.x;
        });
    });
    
    // Create tick values every 5 years for grid lines
    const tickValues = [];
    for (let year = domainMin; year <= domainMax; year += 5) {
        tickValues.push(year);
    }
    
    // Find the y positions of each path for grid lines and backgrounds
    const pathNodes = root.descendants().filter(d => d.data.type === 'path');
    const pathPositions = pathNodes.map(d => ({
        name: d.data.name,
        y: d.y,
        color: d.data.color
    })).sort((a, b) => a.y - b.y);
    
    // Check if dark theme
    const containerEl = container.closest('.cpv-container');
    const isDarkTheme = containerEl && (
        containerEl.classList.contains('cpv-theme-dark') || 
        (containerEl.classList.contains('cpv-theme-system') && window.matchMedia('(prefers-color-scheme: dark)').matches)
    );
    
    // Add colored background bands for each path
    const backgroundBands = svgGroup.append('g')
        .attr('class', 'path-backgrounds');
    
    pathPositions.forEach((path, i) => {
        const nextY = i < pathPositions.length - 1 ? pathPositions[i + 1].y : height;
        const prevY = i > 0 ? pathPositions[i - 1].y : 0;
        const bandTop = (prevY + path.y) / 2;
        const bandBottom = (path.y + nextY) / 2;
        
        // Add colored background band
        backgroundBands.append('rect')
            .attr('x', -margin.left)
            .attr('y', bandTop)
            .attr('width', width + margin.left + margin.right)
            .attr('height', bandBottom - bandTop)
            .style('fill', path.color)
            .style('opacity', isDarkTheme ? 0.35 : 0.25) // Brighter for dark theme
            .style('filter', isDarkTheme ? 'brightness(1.3)' : 'none'); // Make colors brighter in dark theme
    });
    
    // Add horizontal grid lines to separate paths
    const gridLines = svgGroup.append('g')
        .attr('class', 'grid-lines');
    
    pathPositions.forEach((path, i) => {
        if (i > 0) {
            const prevPath = pathPositions[i - 1];
            const gridY = (prevPath.y + path.y) / 2;
            
            gridLines.append('line')
                .attr('x1', -margin.left)
                .attr('x2', width + 20)
                .attr('y1', gridY)
                .attr('y2', gridY)
                .style('stroke', '#999')
                .style('stroke-width', 1)
                .style('opacity', 0.3);
        }
    });
    
    // Add vertical grid lines for years
    tickValues.forEach(year => {
        gridLines.append('line')
            .attr('x1', timeScale(year))
            .attr('x2', timeScale(year))
            .attr('y1', 0)
            .attr('y2', height)
            .style('stroke', '#ddd')
            .style('stroke-dasharray', '3, 3')
            .style('stroke-width', 0.5)
            .style('opacity', 0.5);
    });
    
    // Create gradient definitions
    const defs = svg.append('defs');
    
    const gradients = [
        { id: 'it-to-design', start: '#4299e1', end: '#ed8936' },
        { id: 'it-to-engineering', start: '#4299e1', end: '#48bb78' },
        { id: 'design-to-engineering', start: '#ed8936', end: '#48bb78' }
    ];
    
    gradients.forEach(grad => {
        const gradient = defs.append('linearGradient')
            .attr('id', grad.id)
            .attr('x1', '0%')
            .attr('y1', '0%')
            .attr('x2', '100%')
            .attr('y2', '0%');
        
        gradient.append('stop')
            .attr('offset', '0%')
            .attr('stop-color', grad.start)
            .attr('stop-opacity', 0.8);
        
        gradient.append('stop')
            .attr('offset', '100%')
            .attr('stop-color', grad.end)
            .attr('stop-opacity', 0.8);
    });
    
    // Create curved links
    const linkGenerator = d3.linkVertical()
        .x(d => d.x)
        .y(d => d.y);
    
    // Draw links
    svgGroup.selectAll('.link')
        .data(root.links())
        .enter()
        .append('path')
        .attr('class', 'link')
        .attr('d', linkGenerator)
        .style('fill', 'none')
        .style('stroke', d => {
            const pathNode = d.target.ancestors().find(ancestor => ancestor.data.type === 'path');
            return pathNode ? pathNode.data.color : '#999';
        })
        .style('stroke-width', d => {
            if (d.target.data.type === 'path') return 3;
            return 2;
        })
        .style('opacity', 0.6);
    
    // Create nodes
    const nodes = svgGroup.selectAll('.node')
        .data(root.descendants())
        .enter()
        .append('g')
        .attr('class', 'node')
        .attr('transform', d => `translate(${d.x},${d.y})`)
        .style('cursor', 'pointer')
        .on('click', function(event, d) {
            event.stopPropagation();
            showNodeInfo(d.data);
        });
    
    // Add circles for nodes
    nodes.append('circle')
        .attr('r', d => {
            if (d.data.type === 'path') return 0;  // Hide path nodes since labels are on left
            if (d.data.name === 'Career Journey') return 0;  // Hide root node
            return 6;
        })
        .style('fill', d => {
            if (d.data.type === 'path') return d.data.color;
            if (d.data.name === 'Career Journey') return '#2d3748';
            const pathNode = d.ancestors().find(ancestor => ancestor.data.type === 'path');
            return pathNode ? pathNode.data.color : '#e2e8f0';
        })
        .style('stroke', '#fff')
        .style('stroke-width', 2);
    
    // Add labels - hide job labels by default to prevent overlap
    const labels = nodes.append('text')
        .attr('x', d => {
            if (d.data.type === 'path') {
                // Position path labels at the left edge of their band
                return -margin.left + 10;
            }
            return 10; // Always position labels to the right of nodes
        })
        .attr('y', d => {
            if (d.data.type === 'path') {
                // Find the band boundaries for this path
                const pathIndex = pathPositions.findIndex(p => p.name === d.data.name);
                if (pathIndex >= 0) {
                    const prevY = pathIndex > 0 ? pathPositions[pathIndex - 1].y : 0;
                    const bandTop = (prevY + d.y) / 2;
                    // Position at top of band with padding
                    return bandTop - d.y + 20;
                }
                return -20;
            }
            return 4;
        })
        .style('text-anchor', d => {
            if (d.data.type === 'path') return 'start';
            return 'start'; // Always start anchor for consistency
        })
        .style('font-size', d => {
            if (d.data.type === 'path') return '14px';
            return '9px'; // Smaller font for job labels
        })
        .style('font-weight', d => d.data.type === 'path' ? 'bold' : 'normal')
        .style('fill', d => {
            if (d.data.type === 'path') return d.data.color || '#666';
            return ''; // Let CSS handle the color
        })
        .attr('class', 'node-label')
        .style('display', d => {
            // Only show path labels by default, hide job labels
            if (d.data.type === 'path' || d.data.name === 'Career Journey') {
                return 'block';
            }
            return 'none';
        })
        .text(d => {
            if (d.data.name === 'Career Journey') return '';
            if (d.data.type === 'path') return d.data.name;
            // Keep full text for jobs (will be hidden anyway)
            if (d.data.type === 'job' && d.data.title) {
                return d.data.title;
            }
            return d.data.title || d.data.name;
        });
    
    // Add tooltips separately (can't append title to text)
    nodes.append('title')
        .text(d => {
            if (d.data.type === 'job' && d.data.title && d.data.name) {
                return `${d.data.title} at ${d.data.name}`;
            }
            return '';
        });
    
    // Add year axis (now on bottom since timeline is horizontal)
    const yearAxis = d3.axisBottom(timeScale)
        .tickFormat(d3.format('d'))
        .tickValues(tickValues);
    
    svgGroup.append('g')
        .attr('class', 'year-axis')
        .attr('transform', `translate(0, ${height})`)
        .call(yearAxis)
        .style('font-size', '10px');
    
    // Auto-fit the entire tree in view on initial load
    setTimeout(() => {
        // Calculate the bounding box of all nodes
        const allNodes = root.descendants().filter(d => d.data.type !== 'path' && d.data.name !== 'Career Journey');
        if (allNodes.length === 0) return;
        
        const xExtent = d3.extent(allNodes, d => d.x);
        const yExtent = d3.extent(allNodes, d => d.y);
        
        // Add 20px padding as requested
        const padding = 20;
        const xRange = xExtent[1] - xExtent[0] + (padding * 2);
        const yRange = yExtent[1] - yExtent[0] + (padding * 2);
        
        // Calculate scale to fit container (not just square)
        const scaleX = width / xRange;
        const scaleY = height / yRange;
        const scale = Math.min(scaleX, scaleY, 2); // Allow up to 2x zoom
        
        // Calculate center
        const centerX = (xExtent[0] + xExtent[1]) / 2;
        const centerY = (yExtent[0] + yExtent[1]) / 2;
        
        // Apply transform to fit all content
        const transform = d3.zoomIdentity
            .translate(width / 2 + margin.left, height / 2 + margin.top)
            .scale(scale)
            .translate(-centerX, -centerY);
        
        svg.call(zoomBehavior.transform, transform);
    }, 100);
}

function createTimelineVisualization(chartId, data) {
    const container = document.getElementById(chartId);
    d3.select(container).selectAll('svg').remove();
    
    const containerWidth = container.offsetWidth || 800;
    const containerHeight = parseInt(container.parentElement.querySelector('.cpv-chart').style.height) || 600;
    
    const margin = { top: 40, right: 40, bottom: 60, left: 100 };
    const width = containerWidth - margin.left - margin.right;
    const height = containerHeight - margin.top - margin.bottom;
    
    const svg = d3.select(container)
        .append('svg')
        .attr('width', containerWidth)
        .attr('height', containerHeight);
    
    zoomBehavior = d3.zoom()
        .scaleExtent([0.5, 3])
        .on('zoom', (event) => {
            svgGroup.attr('transform', event.transform);
        });
    
    svg.call(zoomBehavior);
    
    svgGroup = svg.append('g')
        .attr('transform', `translate(${margin.left},${margin.top})`);
    
    // Extract all jobs from the hierarchical data
    const jobs = [];
    function extractJobs(node, path = '') {
        if (node.type === 'job') {
            jobs.push({
                ...node,
                path: path,
                startDate: new Date(node.startYear || 2003, 0),
                endDate: new Date(node.endYear || 2025, 0)
            });
        }
        if (node.children) {
            node.children.forEach(child => extractJobs(child, node.type === 'path' ? node.name : path));
        }
    }
    extractJobs(data);
    
    // Sort jobs by start date
    jobs.sort((a, b) => a.startDate - b.startDate);
    
    // Create scales
    const xScale = d3.scaleTime()
        .domain([new Date(2003, 0), new Date(2025, 0)])
        .range([0, width]);
    
    const paths = [...new Set(jobs.map(j => j.path))];
    const yScale = d3.scaleBand()
        .domain(paths)
        .range([0, height])
        .padding(0.2);
    
    // Draw timeline bars
    svgGroup.selectAll('.timeline-bar')
        .data(jobs)
        .enter()
        .append('rect')
        .attr('class', 'timeline-bar')
        .attr('x', d => xScale(d.startDate))
        .attr('y', d => yScale(d.path))
        .attr('width', d => xScale(d.endDate) - xScale(d.startDate))
        .attr('height', yScale.bandwidth())
        .style('fill', d => {
            const pathColors = {
                'IT Path': '#4299e1',
                'Design Path': '#ed8936',
                'Engineering Path': '#48bb78'
            };
            return pathColors[d.path] || '#999';
        })
        .style('opacity', 0.7)
        .style('cursor', 'pointer')
        .on('click', function(event, d) {
            showNodeInfo(d);
        });
    
    // Add job labels
    svgGroup.selectAll('.timeline-label')
        .data(jobs)
        .enter()
        .append('text')
        .attr('class', 'timeline-label')
        .attr('x', d => xScale(d.startDate) + 5)
        .attr('y', d => yScale(d.path) + yScale.bandwidth() / 2)
        .attr('dy', '0.35em')
        .style('font-size', '10px')
        .style('fill', 'white')
        .text(d => d.name);
    
    // Add axes
    const xAxis = d3.axisBottom(xScale);
    svgGroup.append('g')
        .attr('transform', `translate(0,${height})`)
        .call(xAxis);
    
    const yAxis = d3.axisLeft(yScale);
    svgGroup.append('g')
        .call(yAxis);
}

function createGraphVisualization(chartId, data) {
    const container = document.getElementById(chartId);
    d3.select(container).selectAll('svg').remove();
    
    const containerWidth = container.offsetWidth || 800;
    const containerHeight = parseInt(container.parentElement.querySelector('.cpv-chart').style.height) || 600;
    
    const svg = d3.select(container)
        .append('svg')
        .attr('width', containerWidth)
        .attr('height', containerHeight);
    
    zoomBehavior = d3.zoom()
        .scaleExtent([0.5, 3])
        .on('zoom', (event) => {
            svgGroup.attr('transform', event.transform);
        });
    
    svg.call(zoomBehavior);
    
    svgGroup = svg.append('g');
    
    // Convert hierarchical data to nodes and links for force layout
    const nodes = [];
    const links = [];
    
    function processNode(node, parent = null) {
        const nodeId = nodes.length;
        nodes.push({
            id: nodeId,
            ...node,
            x: containerWidth / 2,
            y: containerHeight / 2
        });
        
        if (parent !== null) {
            links.push({
                source: parent,
                target: nodeId
            });
        }
        
        if (node.children) {
            node.children.forEach(child => processNode(child, nodeId));
        }
    }
    
    processNode(data);
    
    // Create force simulation
    const simulation = d3.forceSimulation(nodes)
        .force('link', d3.forceLink(links).id(d => d.id).distance(100))
        .force('charge', d3.forceManyBody().strength(-300))
        .force('center', d3.forceCenter(containerWidth / 2, containerHeight / 2))
        .force('collision', d3.forceCollide().radius(30));
    
    // Draw links
    const link = svgGroup.selectAll('.link')
        .data(links)
        .enter()
        .append('line')
        .attr('class', 'link')
        .style('stroke', '#999')
        .style('stroke-width', 2)
        .style('opacity', 0.6);
    
    // Draw nodes
    const node = svgGroup.selectAll('.node')
        .data(nodes)
        .enter()
        .append('g')
        .attr('class', 'node')
        .call(d3.drag()
            .on('start', dragstarted)
            .on('drag', dragged)
            .on('end', dragended));
    
    node.append('circle')
        .attr('r', d => {
            if (d.type === 'path') return 15;
            if (d.type === 'root') return 12;
            return 8;
        })
        .style('fill', d => {
            if (d.type === 'path') return d.color;
            if (d.type === 'root') return '#2d3748';
            return '#4299e1';
        })
        .style('stroke', '#fff')
        .style('stroke-width', 2);
    
    node.append('text')
        .attr('dx', 12)
        .attr('dy', '0.35em')
        .style('font-size', '10px')
        .text(d => d.title || d.name);
    
    // Update positions on tick
    simulation.on('tick', () => {
        link
            .attr('x1', d => d.source.x)
            .attr('y1', d => d.source.y)
            .attr('x2', d => d.target.x)
            .attr('y2', d => d.target.y);
        
        node.attr('transform', d => `translate(${d.x},${d.y})`);
    });
    
    function dragstarted(event, d) {
        if (!event.active) simulation.alphaTarget(0.3).restart();
        d.fx = d.x;
        d.fy = d.y;
    }
    
    function dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
    }
    
    function dragended(event, d) {
        if (!event.active) simulation.alphaTarget(0);
        d.fx = null;
        d.fy = null;
    }
}

function createSankeyVisualization(chartId, data) {
    const container = document.getElementById(chartId);
    d3.select(container).selectAll('svg').remove();
    
    // For Sankey, we'll create a simplified flow diagram
    // This is a placeholder - full Sankey implementation would require d3-sankey plugin
    const message = document.createElement('div');
    message.style.padding = '20px';
    message.style.textAlign = 'center';
    message.innerHTML = '<p>Sankey diagram view coming soon!</p><p>Please use Tree, Timeline, or Graph view.</p>';
    container.appendChild(message);
}

function showNodeInfo(nodeData) {
    if (nodeData.type === 'root' || nodeData.type === 'path') return;
    
    selectedNode = nodeData;
    const container = document.getElementById(currentChartId).parentElement;
    
    // Remove existing info panel
    const existingPanel = container.querySelector('.cpv-info-panel');
    if (existingPanel) {
        existingPanel.remove();
    }
    
    // Create info panel
    const infoPanel = document.createElement('div');
    infoPanel.className = 'cpv-info-panel';
    infoPanel.style.position = 'fixed';
    infoPanel.style.top = '50%';
    infoPanel.style.left = '50%';
    infoPanel.style.transform = 'translate(-50%, -50%)';
    infoPanel.style.background = 'white';
    infoPanel.style.padding = '20px';
    infoPanel.style.borderRadius = '8px';
    infoPanel.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
    infoPanel.style.zIndex = '1000';
    infoPanel.style.maxWidth = '400px';
    
    let infoPanelHTML = `
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
            <div>
                <h3 style="margin: 0;">${nodeData.title || nodeData.name}</h3>
                ${nodeData.name && nodeData.title ? `<p style="margin: 5px 0; color: #666;">${nodeData.name}</p>` : ''}
            </div>
            <button onclick="closeNodeInfo()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>`;
    
    if (nodeData.dates) {
        infoPanelHTML += `<p><strong>Dates:</strong> ${nodeData.dates}</p>`;
    }
    
    if (nodeData.description) {
        infoPanelHTML += `<p>${nodeData.description}</p>`;
    }
    
    if (nodeData.skills && nodeData.skills.length > 0) {
        infoPanelHTML += `<p><strong>Skills:</strong> ${nodeData.skills.join(', ')}</p>`;
    }
    
    infoPanel.innerHTML = infoPanelHTML;
    document.body.appendChild(infoPanel);
    
    // Add backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'cpv-backdrop';
    backdrop.style.position = 'fixed';
    backdrop.style.top = '0';
    backdrop.style.left = '0';
    backdrop.style.width = '100%';
    backdrop.style.height = '100%';
    backdrop.style.background = 'rgba(0, 0, 0, 0.5)';
    backdrop.style.zIndex = '999';
    backdrop.onclick = closeNodeInfo;
    document.body.appendChild(backdrop);
    
    // Add ESC key handler
    function handleEscKey(event) {
        if (event.key === 'Escape' || event.keyCode === 27) {
            closeNodeInfo();
            document.removeEventListener('keydown', handleEscKey);
        }
    }
    document.addEventListener('keydown', handleEscKey);
}

function closeNodeInfo() {
    const infoPanel = document.querySelector('.cpv-info-panel');
    const backdrop = document.querySelector('.cpv-backdrop');
    
    if (infoPanel) infoPanel.remove();
    if (backdrop) backdrop.remove();
    
    // Remove ESC key handler if it exists
    document.removeEventListener('keydown', handleEscKey);
    
    selectedNode = null;
}

// Global reference for ESC key handler
function handleEscKey(event) {
    if (event.key === 'Escape' || event.keyCode === 27) {
        closeNodeInfo();
    }
}

function setupControls(chartId) {
    const container = document.getElementById(chartId).parentElement;
    
    // Zoom In
    container.querySelector('[data-action="zoom-in"]')?.addEventListener('click', () => {
        if (zoomBehavior && svgGroup) {
            const svg = d3.select(`#${chartId} svg`);
            svg.transition().duration(750).call(
                zoomBehavior.scaleBy, 1.3
            );
        }
    });
    
    // Zoom Out
    container.querySelector('[data-action="zoom-out"]')?.addEventListener('click', () => {
        if (zoomBehavior && svgGroup) {
            const svg = d3.select(`#${chartId} svg`);
            svg.transition().duration(750).call(
                zoomBehavior.scaleBy, 0.7
            );
        }
    });
}

// Handle window resize
let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        if (currentChartId && careerData) {
            updateVisualization(currentView);
        }
    }, 250);
});